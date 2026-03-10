<?php
declare(strict_types=1);

class MaterialService
{
    public function list(string $sort = 'name', string $order = 'asc', array $filters = []): ServiceResult
    {
        $materials = Material::allWithGameCount($sort, $order, $filters);
        return ServiceResult::ok(['materials' => $materials]);
    }

    public function get(int $id): ServiceResult
    {
        $material = Material::findWithGameCount($id);
        if (!$material) {
            return ServiceResult::fail([], __('material.not_found'));
        }
        $games = Material::getGames($id);
        return ServiceResult::ok(['material' => $material, 'games' => $games]);
    }

    public function create(array $data): ServiceResult
    {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return ServiceResult::fail($errors);
        }

        if (!empty($data['name']) && Material::nameExists($data['name'])) {
            return ServiceResult::fail(['name' => [__('validation.duplicate')]]);
        }

        $materialId = Material::create($data);
        if (!$materialId) {
            return ServiceResult::fail([], __('flash.error_creating'));
        }

        ChangelogService::getInstance()->logCreate('material', $materialId, $data['name'], $data);

        return ServiceResult::ok(
            ['id' => $materialId],
            __('flash.created', ['item' => __('material.title')])
        );
    }

    public function update(int $id, array $data): ServiceResult
    {
        $material = Material::find($id);
        if (!$material) {
            return ServiceResult::fail([], __('material.not_found'));
        }

        $errors = $this->validate($data, $id);
        if (!empty($errors)) {
            return ServiceResult::fail($errors);
        }

        if (!empty($data['name']) && Material::nameExists($data['name'], $id)) {
            return ServiceResult::fail(['name' => [__('validation.duplicate')]]);
        }

        $changelog = ChangelogService::getInstance();
        $changes = $changelog->getChanges($material, $data, ['name', 'description', 'image_path', 'quantity', 'is_consumable']);

        Material::update($id, $data);

        if (!empty($changes)) {
            $changelog->logUpdate('material', $id, $data['name'], $changes);
        }

        return ServiceResult::ok(
            ['id' => $id],
            __('flash.updated', ['item' => __('material.title')])
        );
    }

    public function delete(int $id): ServiceResult
    {
        $material = Material::find($id);
        if (!$material) {
            return ServiceResult::fail([], __('material.not_found'));
        }

        ChangelogService::getInstance()->logDelete('material', $id, $material['name'], $material);
        Material::delete($id);

        if ($material['image_path']) {
            (new ImageProcessor())->delete($material['image_path']);
        }

        return ServiceResult::ok(
            ['id' => $id],
            __('flash.deleted', ['item' => __('material.title')])
        );
    }

    public function quickCreate(string $name): ServiceResult
    {
        if (empty($name)) {
            return ServiceResult::fail(['name' => [__('validation.name_required')]]);
        }
        if (mb_strlen($name) > 100) {
            return ServiceResult::fail(['name' => [__('validation.name_max_100')]]);
        }
        if (Material::nameExists($name)) {
            return ServiceResult::fail(['name' => [__('validation.duplicate')]]);
        }

        $materialId = Material::quickCreate($name);
        if (!$materialId) {
            return ServiceResult::fail([], __('flash.error_creating'));
        }

        ChangelogService::getInstance()->logCreate('material', $materialId, $name, ['name' => $name]);

        return ServiceResult::ok(
            ['id' => $materialId, 'name' => $name],
            __('flash.created', ['item' => __('material.title')])
        );
    }

    private function validate(array $data, ?int $excludeId = null): array
    {
        $validator = Validator::make($data, [
            'name' => 'required|max:100',
        ]);
        return $validator->errors();
    }
}
