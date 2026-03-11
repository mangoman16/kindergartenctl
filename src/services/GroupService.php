<?php
declare(strict_types=1);

class GroupService
{
    public function list(): ServiceResult
    {
        $groups = Group::allWithCounts();
        return ServiceResult::ok(['groups' => $groups]);
    }

    public function get(int $id): ServiceResult
    {
        $group = Group::findWithCounts($id);
        if (!$group) return ServiceResult::fail([], __('group.not_found'));

        $games = Group::getGames($id);
        $materials = Group::getMaterials($id);

        return ServiceResult::ok([
            'group' => $group,
            'games' => $games,
            'materials' => $materials,
        ]);
    }

    public function create(array $data, array $gameIds = [], array $materials = []): ServiceResult
    {
        $errors = $this->validate($data);
        if (!empty($errors)) return ServiceResult::fail($errors);

        if (!empty($data['name']) && Group::nameExists($data['name'])) {
            return ServiceResult::fail(['name' => [__('validation.duplicate')]]);
        }

        $groupId = Group::create($data);
        if (!$groupId) return ServiceResult::fail([], __('flash.error_creating'));

        Group::updateGames($groupId, $gameIds);
        Group::updateMaterials($groupId, $materials);

        ChangelogService::getInstance()->logCreate('group', $groupId, $data['name'], $data);

        return ServiceResult::ok(
            ['id' => $groupId],
            __('flash.created', ['item' => __('group.title')])
        );
    }

    public function update(int $id, array $data, array $gameIds = [], array $materials = []): ServiceResult
    {
        $group = Group::find($id);
        if (!$group) return ServiceResult::fail([], __('group.not_found'));

        $errors = $this->validate($data, $id);
        if (!empty($errors)) return ServiceResult::fail($errors);

        if (!empty($data['name']) && Group::nameExists($data['name'], $id)) {
            return ServiceResult::fail(['name' => [__('validation.duplicate')]]);
        }

        $changelog = ChangelogService::getInstance();
        $changes = $changelog->getChanges($group, $data, ['name', 'description', 'image_path']);

        Group::update($id, $data);
        Group::updateGames($id, $gameIds);
        Group::updateMaterials($id, $materials);

        if (!empty($changes)) {
            $changelog->logUpdate('group', $id, $data['name'], $changes);
        }

        return ServiceResult::ok(
            ['id' => $id],
            __('flash.updated', ['item' => __('group.title')])
        );
    }

    public function delete(int $id): ServiceResult
    {
        $group = Group::find($id);
        if (!$group) return ServiceResult::fail([], __('group.not_found'));

        ChangelogService::getInstance()->logDelete('group', $id, $group['name'], $group);
        Group::delete($id);

        if ($group['image_path']) {
            (new ImageProcessor())->delete($group['image_path']);
        }

        return ServiceResult::ok(
            ['id' => $id],
            __('flash.deleted', ['item' => __('group.title')])
        );
    }

    public function addItem(int $groupId, string $itemType, int $itemId): ServiceResult
    {
        if (!in_array($itemType, ['game', 'material'], true)) {
            return ServiceResult::fail([], __('api.invalid_item_type'));
        }
        $result = Group::addItem($groupId, $itemType, $itemId);
        return $result ? ServiceResult::ok() : ServiceResult::fail([], __('api.add_item_failed'));
    }

    public function removeItem(int $groupId, string $itemType, int $itemId): ServiceResult
    {
        if (!in_array($itemType, ['game', 'material'], true)) {
            return ServiceResult::fail([], __('api.invalid_item_type'));
        }
        $result = Group::removeItem($groupId, $itemType, $itemId);
        return $result ? ServiceResult::ok() : ServiceResult::fail([], __('api.remove_item_failed'));
    }

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

    private function validate(array $data, ?int $excludeId = null): array
    {
        $validator = Validator::make($data, ['name' => 'required|max:100']);
        return $validator->errors();
    }
}
