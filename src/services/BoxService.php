<?php
declare(strict_types=1);

class BoxService
{
    public function list(string $sort = 'name', string $direction = 'ASC'): ServiceResult
    {
        $allowedSort = ['name', 'number', 'location', 'created_at'];
        if (!in_array($sort, $allowedSort)) $sort = 'name';
        if (!in_array($direction, ['ASC', 'DESC'])) $direction = 'ASC';

        $boxes = Box::allWithMaterialCount($sort, $direction);
        return ServiceResult::ok(['boxes' => $boxes]);
    }

    public function get(int $id): ServiceResult
    {
        $box = Box::findWithMaterialCount($id);
        if (!$box) {
            return ServiceResult::fail([], __('box.not_found'));
        }
        $materials = Box::getMaterials($id);
        return ServiceResult::ok(['box' => $box, 'materials' => $materials]);
    }

    public function create(array $data): ServiceResult
    {
        $errors = $this->validate($data);
        if (!empty($errors)) return ServiceResult::fail($errors);

        if (!empty($data['name']) && Box::nameExists($data['name'])) {
            return ServiceResult::fail(['name' => [__('validation.duplicate')]]);
        }

        $boxId = Box::create($data);
        if (!$boxId) {
            return ServiceResult::fail([], __('flash.error_generic'));
        }

        ChangelogService::getInstance()->logCreate('box', $boxId, $data['name'], $data);

        return ServiceResult::ok(
            ['id' => $boxId],
            __('flash.created', ['item' => __('box.title')])
        );
    }

    public function update(int $id, array $data): ServiceResult
    {
        $box = Box::find($id);
        if (!$box) return ServiceResult::fail([], __('box.not_found'));

        $errors = $this->validate($data, $id);
        if (!empty($errors)) return ServiceResult::fail($errors);

        if (!empty($data['name']) && Box::nameExists($data['name'], $id)) {
            return ServiceResult::fail(['name' => [__('validation.duplicate')]]);
        }

        $changelog = ChangelogService::getInstance();
        $changes = $changelog->getChanges($box, $data, ['name', 'number', 'label', 'location_id', 'description', 'notes', 'image_path']);

        Box::update($id, $data);
        $changelog->logUpdate('box', $id, $data['name'], $changes);

        return ServiceResult::ok(
            ['id' => $id],
            __('flash.updated', ['item' => __('box.title')])
        );
    }

    public function delete(int $id): ServiceResult
    {
        $box = Box::find($id);
        if (!$box) return ServiceResult::fail([], __('box.not_found'));

        ChangelogService::getInstance()->logDelete('box', $id, $box['name'], $box);
        Box::delete($id);

        if ($box['image_path']) {
            (new ImageProcessor())->delete($box['image_path']);
        }

        return ServiceResult::ok(
            ['id' => $id],
            __('flash.deleted', ['item' => __('box.title')])
        );
    }

    private function validate(array $data, ?int $excludeId = null): array
    {
        $validator = Validator::make($data, [
            'name' => 'required|max:100',
            'number' => 'max:20',
            'label' => 'max:50',
        ]);
        return $validator->errors();
    }
}
