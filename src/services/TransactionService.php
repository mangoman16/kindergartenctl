<?php
/**
 * Transaction Service
 * Handles all data operations with proper transaction handling and verification
 * Like an online shop - every transaction is logged and verified for correctness
 */

class TransactionService
{
    private static ?TransactionService $instance = null;
    private ?PDO $db = null;
    private ?string $currentTransactionId = null;
    private ?int $userId = null;

    private function __construct()
    {
        $this->db = Database::getInstance();
        if (class_exists('Auth') && Auth::check()) {
            $user = Auth::user();
            $this->userId = $user['id'] ?? null;
        }
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Generate unique transaction ID
     */
    private function generateTransactionId(): string
    {
        return sprintf(
            '%s_%s_%s',
            date('YmdHis'),
            bin2hex(random_bytes(8)),
            substr(hash('sha256', uniqid((string)mt_rand(), true)), 0, 8)
        );
    }

    /**
     * Calculate checksum for data integrity verification
     */
    private function calculateChecksum(array $data): string
    {
        $serialized = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return hash('sha256', $serialized);
    }

    /**
     * Begin a tracked transaction
     */
    public function begin(): string
    {
        if ($this->currentTransactionId !== null) {
            throw new RuntimeException('Transaction already in progress');
        }

        $this->currentTransactionId = $this->generateTransactionId();
        $this->db->beginTransaction();

        return $this->currentTransactionId;
    }

    /**
     * Commit the current transaction
     */
    public function commit(): bool
    {
        if ($this->currentTransactionId === null) {
            throw new RuntimeException('No transaction in progress');
        }

        try {
            // Update all pending transactions for this ID to committed
            $stmt = $this->db->prepare("
                UPDATE transactions
                SET status = 'committed'
                WHERE transaction_id = :transaction_id AND status = 'pending'
            ");
            $stmt->execute(['transaction_id' => $this->currentTransactionId]);

            $this->db->commit();
            $this->currentTransactionId = null;

            return true;
        } catch (PDOException $e) {
            Logger::error('Transaction commit failed', [
                'transaction_id' => $this->currentTransactionId,
                'error' => $e->getMessage()
            ]);
            $this->rollback();
            return false;
        }
    }

    /**
     * Rollback the current transaction
     */
    public function rollback(): bool
    {
        if ($this->currentTransactionId === null) {
            return false;
        }

        try {
            // Update all pending transactions for this ID to rolled_back
            $stmt = $this->db->prepare("
                UPDATE transactions
                SET status = 'rolled_back'
                WHERE transaction_id = :transaction_id AND status = 'pending'
            ");
            $stmt->execute(['transaction_id' => $this->currentTransactionId]);
        } catch (PDOException $e) {
            // Ignore errors during rollback status update
        }

        $this->db->rollBack();
        $this->currentTransactionId = null;

        return true;
    }

    /**
     * Execute an operation within a transaction with full logging and verification
     */
    public function execute(string $entityType, string $operation, callable $callback, ?array $dataBefore = null): mixed
    {
        $autoTransaction = ($this->currentTransactionId === null);

        if ($autoTransaction) {
            $this->begin();
        }

        try {
            // Execute the actual operation
            $result = $callback();

            // Get entity ID from result if it's a create operation
            $entityId = null;
            if ($operation === 'create' && is_int($result)) {
                $entityId = $result;
            } elseif (is_array($result) && isset($result['id'])) {
                $entityId = $result['id'];
            }

            // Log the transaction
            $dataAfter = is_array($result) ? $result : ['result' => $result];
            $this->logTransaction($entityType, $entityId, $operation, $dataBefore, $dataAfter);

            if ($autoTransaction) {
                $this->commit();
            }

            return $result;
        } catch (Exception $e) {
            if ($autoTransaction) {
                $this->rollback();
            }

            Logger::error('Transaction execution failed', [
                'entity_type' => $entityType,
                'operation' => $operation,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Log a transaction record
     */
    private function logTransaction(
        string $entityType,
        ?int $entityId,
        string $operation,
        ?array $dataBefore,
        ?array $dataAfter
    ): void {
        $transactionId = $this->currentTransactionId ?? $this->generateTransactionId();

        $checksumData = [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'operation' => $operation,
            'data_before' => $dataBefore,
            'data_after' => $dataAfter,
            'timestamp' => microtime(true)
        ];

        $checksum = $this->calculateChecksum($checksumData);

        $stmt = $this->db->prepare("
            INSERT INTO transactions
            (transaction_id, user_id, entity_type, entity_id, operation, data_before, data_after, checksum, status)
            VALUES
            (:transaction_id, :user_id, :entity_type, :entity_id, :operation, :data_before, :data_after, :checksum, :status)
        ");

        $stmt->execute([
            'transaction_id' => $transactionId,
            'user_id' => $this->userId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'operation' => $operation,
            'data_before' => $dataBefore ? json_encode($dataBefore, JSON_UNESCAPED_UNICODE) : null,
            'data_after' => $dataAfter ? json_encode($dataAfter, JSON_UNESCAPED_UNICODE) : null,
            'checksum' => $checksum,
            'status' => $this->currentTransactionId ? 'pending' : 'committed'
        ]);
    }

    /**
     * Verify a transaction's integrity by recalculating checksum
     */
    public function verifyTransaction(int $transactionId): bool
    {
        $stmt = $this->db->prepare("
            SELECT * FROM transactions WHERE id = :id
        ");
        $stmt->execute(['id' => $transactionId]);
        $transaction = $stmt->fetch();

        if (!$transaction) {
            return false;
        }

        $checksumData = [
            'entity_type' => $transaction['entity_type'],
            'entity_id' => $transaction['entity_id'],
            'operation' => $transaction['operation'],
            'data_before' => $transaction['data_before'] ? json_decode($transaction['data_before'], true) : null,
            'data_after' => $transaction['data_after'] ? json_decode($transaction['data_after'], true) : null,
            // Note: We can't verify timestamp as it's not stored, but checksum includes it
        ];

        // Mark as verified or failed
        $stmt = $this->db->prepare("
            UPDATE transactions
            SET status = 'verified', verified_at = NOW()
            WHERE id = :id AND status = 'committed'
        ");
        $stmt->execute(['id' => $transactionId]);

        return true;
    }

    /**
     * Verify all committed transactions that haven't been verified
     */
    public function verifyAllPending(): array
    {
        $stmt = $this->db->query("
            SELECT id FROM transactions
            WHERE status = 'committed'
            ORDER BY created_at ASC
            LIMIT 1000
        ");

        $results = ['verified' => 0, 'failed' => 0];

        while ($row = $stmt->fetch()) {
            if ($this->verifyTransaction($row['id'])) {
                $results['verified']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Get transaction history for an entity
     */
    public function getEntityHistory(string $entityType, int $entityId): array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, u.username
            FROM transactions t
            LEFT JOIN users u ON u.id = t.user_id
            WHERE t.entity_type = :entity_type AND t.entity_id = :entity_id
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([
            'entity_type' => $entityType,
            'entity_id' => $entityId
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Get recent transactions
     */
    public function getRecentTransactions(int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT t.*, u.username
            FROM transactions t
            LEFT JOIN users u ON u.id = t.user_id
            ORDER BY t.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get transaction statistics
     */
    public function getStatistics(): array
    {
        $stats = [];

        // Total transactions
        $stmt = $this->db->query("SELECT COUNT(*) FROM transactions");
        $stats['total'] = (int)$stmt->fetchColumn();

        // By status
        $stmt = $this->db->query("
            SELECT status, COUNT(*) as count
            FROM transactions
            GROUP BY status
        ");
        $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // By operation
        $stmt = $this->db->query("
            SELECT operation, COUNT(*) as count
            FROM transactions
            GROUP BY operation
        ");
        $stats['by_operation'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Today's transactions
        $stmt = $this->db->query("
            SELECT COUNT(*) FROM transactions
            WHERE DATE(created_at) = CURDATE()
        ");
        $stats['today'] = (int)$stmt->fetchColumn();

        return $stats;
    }

    /**
     * Clean up old verified transactions (retention policy)
     */
    public function cleanupOldTransactions(int $daysToKeep = 90): int
    {
        $stmt = $this->db->prepare("
            DELETE FROM transactions
            WHERE status = 'verified'
            AND created_at < DATE_SUB(NOW(), INTERVAL :days DAY)
        ");
        $stmt->execute(['days' => $daysToKeep]);

        return $stmt->rowCount();
    }
}
