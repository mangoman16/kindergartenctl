<?php
declare(strict_types=1);

/**
 * GameService - Business logic for games.
 *
 * Shared by web controllers and CLI. Contains validation, CRUD,
 * tag/material relation management, changelog, and transaction wrapping.
 */
class GameService
{
    /**
     * List games with optional filters and sorting.
     */
    public function list(array $filters = [], string $sort = 'name', string $order = 'asc'): ServiceResult
    {
        $games = Game::allWithRelations($filters, $sort, $order);
        return ServiceResult::ok(['games' => $games]);
    }

    /**
     * Get a single game with all relations.
     */
    public function get(int $id): ServiceResult
    {
        $game = Game::findWithRelations($id);
        if (!$game) {
            return ServiceResult::fail([], __('game.not_found'));
        }
        return ServiceResult::ok(['game' => $game]);
    }

    /**
     * Create a new game.
     *
     * @param array $data      Game fields
     * @param array $tagIds    Tag IDs to attach
     * @param array $materials Materials [{id, quantity}, ...]
     */
    public function create(array $data, array $tagIds = [], array $materials = []): ServiceResult
    {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return ServiceResult::fail($errors);
        }

        // Duplicate check
        if (!empty($data['name']) && Game::nameExists($data['name'])) {
            return ServiceResult::fail(['name' => [__('validation.duplicate')]]);
        }

        try {
            $transaction = TransactionService::getInstance();
            $gameId = $transaction->execute('game', 'create', function () use ($data, $tagIds, $materials) {
                $gameId = Game::create($data);
                if (!$gameId) {
                    throw new RuntimeException(__('flash.error_creating'));
                }
                Game::updateTags($gameId, $tagIds);
                Game::updateMaterials($gameId, $materials);
                return $gameId;
            }, null);

            ChangelogService::getInstance()->logCreate('game', $gameId, $data['name'], $data);

            return ServiceResult::ok(
                ['id' => $gameId],
                __('flash.created', ['item' => __('game.title')])
            );
        } catch (Exception $e) {
            return ServiceResult::fail([], $e->getMessage());
        }
    }

    /**
     * Update an existing game.
     */
    public function update(int $id, array $data, array $tagIds = [], array $materials = []): ServiceResult
    {
        $game = Game::find($id);
        if (!$game) {
            return ServiceResult::fail([], __('game.not_found'));
        }

        $errors = $this->validate($data, $id);
        if (!empty($errors)) {
            return ServiceResult::fail($errors);
        }

        // Duplicate check excluding current
        if (!empty($data['name']) && Game::nameExists($data['name'], $id)) {
            return ServiceResult::fail(['name' => [__('validation.duplicate')]]);
        }

        try {
            $changelog = ChangelogService::getInstance();
            $changes = $changelog->getChanges($game, $data, [
                'name', 'description', 'instructions', 'min_players', 'max_players',
                'duration_minutes', 'difficulty', 'is_outdoor', 'is_active',
                'image_path', 'box_id', 'category_id',
            ]);

            $transaction = TransactionService::getInstance();
            $transaction->execute('game', 'update', function () use ($id, $data, $tagIds, $materials) {
                Game::update($id, $data);
                Game::updateTags($id, $tagIds);
                Game::updateMaterials($id, $materials);
                return ['id' => $id];
            }, $game);

            if (!empty($changes)) {
                $changelog->logUpdate('game', $id, $data['name'], $changes);
            }

            return ServiceResult::ok(
                ['id' => $id],
                __('flash.updated', ['item' => __('game.title')])
            );
        } catch (Exception $e) {
            return ServiceResult::fail([], $e->getMessage());
        }
    }

    /**
     * Delete a game (with image cleanup).
     */
    public function delete(int $id): ServiceResult
    {
        $game = Game::find($id);
        if (!$game) {
            return ServiceResult::fail([], __('game.not_found'));
        }

        try {
            $transaction = TransactionService::getInstance();
            $transaction->execute('game', 'delete', function () use ($id, $game) {
                ChangelogService::getInstance()->logDelete('game', $id, $game['name'], $game);
                Game::delete($id);
                if ($game['image_path']) {
                    (new ImageProcessor())->delete($game['image_path']);
                }
                return ['id' => $id, 'deleted' => true];
            }, $game);

            return ServiceResult::ok(
                ['id' => $id],
                __('flash.deleted', ['item' => __('game.title')])
            );
        } catch (Exception $e) {
            return ServiceResult::fail([], $e->getMessage());
        }
    }

    /**
     * Duplicate a game.
     */
    public function duplicate(int $id): ServiceResult
    {
        $game = Game::find($id);
        if (!$game) {
            return ServiceResult::fail([], __('game.not_found'));
        }

        try {
            $transaction = TransactionService::getInstance();
            $newGameId = $transaction->execute('game', 'create', function () use ($id, $game) {
                $newGameId = Game::duplicate($id);
                if (!$newGameId) {
                    throw new RuntimeException(__('flash.error_duplicating'));
                }
                $newGame = Game::find($newGameId);
                ChangelogService::getInstance()->logCreate(
                    'game', $newGameId, $newGame['name'],
                    ['duplicated_from' => $game['name']]
                );
                return $newGameId;
            }, $game);

            return ServiceResult::ok(
                ['id' => $newGameId],
                __('flash.duplicated', ['item' => __('game.title')])
            );
        } catch (Exception $e) {
            return ServiceResult::fail([], $e->getMessage());
        }
    }

    /**
     * Get filter option lists (boxes, categories, tags) for the games index.
     */
    public function getFilterOptions(): array
    {
        return [
            'boxes' => Box::getForSelect(),
            'categories' => Category::getForSelect(),
            'tags' => Tag::getForSelect(),
        ];
    }

    /**
     * Parse materials from form input array.
     */
    public static function parseMaterials(array $materialsInput): array
    {
        $materials = [];
        foreach ($materialsInput as $item) {
            if (is_array($item) && isset($item['id'])) {
                $materials[] = [
                    'id' => (int)$item['id'],
                    'quantity' => isset($item['quantity']) ? (int)$item['quantity'] : 1,
                ];
            } elseif (is_numeric($item)) {
                $materials[] = ['id' => (int)$item, 'quantity' => 1];
            }
        }
        return $materials;
    }

    // ------------------------------------------------------------------
    // Validation
    // ------------------------------------------------------------------

    private function validate(array $data, ?int $excludeId = null): array
    {
        $validator = Validator::make($data, [
            'name' => 'required|max:255',
            'description' => 'max:10000',
            'instructions' => 'max:50000',
            'min_players' => 'integer|minValue:1|maxValue:999',
            'max_players' => 'integer|minValue:1|maxValue:999',
            'duration_minutes' => 'integer|minValue:1|maxValue:9999',
            'difficulty' => 'integer|minValue:1|maxValue:5',
        ]);

        if (($data['min_players'] ?? null) !== null && ($data['max_players'] ?? null) !== null) {
            if ((int)$data['max_players'] < (int)$data['min_players']) {
                $validator->addError('max_players', __('validation.max_gte_min_players'));
            }
        }

        return $validator->errors();
    }
}
