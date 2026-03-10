<?php
declare(strict_types=1);

class LocationService
{
    public function list(string $sort = 'name', string $direction = 'ASC'): ServiceResult
    {
        $allowedSort = ['name', 'created_at'];
        if (!in_array($sort, $allowedSort)) $sort = 'name';
        if (!in_array($direction, ['ASC', 'DESC'])) $direction = 'ASC';

        $locations = Location::allWithBoxCount($sort, $direction);
        return ServiceResult::ok(['locations' => $locations]);
    }

    public function get(int $id): ServiceResult
    {
        $location = Location::findWithBoxCount($id);
        if (!$location) return ServiceResult::fail([], __('location.not_found'));

        $boxes = Location::getBoxes($id);
        return ServiceResult::ok(['location' => $location, 'boxes' => $boxes]);
    }

    public function create(array $data): ServiceResult
    {
        $errors = $this->validate($data);
        if (!empty($errors)) return ServiceResult::fail($errors);

        if (!empty($data['name']) && Location::nameExists($data['name'])) {
            return ServiceResult::fail(['name' => [__('validation.duplicate')]]);
        }

        $locationId = Location::create($data);
        if (!$locationId) return ServiceResult::fail([], __('flash.error_generic'));

        ChangelogService::getInstance()->logCreate('location', $locationId, $data['name'], $data);

        return ServiceResult::ok(
            ['id' => $locationId],
            __('flash.created', ['item' => __('location.title')])
        );
    }

    public function update(int $id, array $data): ServiceResult
    {
        $location = Location::find($id);
        if (!$location) return ServiceResult::fail([], __('location.not_found'));

        $errors = $this->validate($data, $id);
        if (!empty($errors)) return ServiceResult::fail($errors);

        if (!empty($data['name']) && Location::nameExists($data['name'], $id)) {
            return ServiceResult::fail(['name' => [__('validation.duplicate')]]);
        }

        $changelog = ChangelogService::getInstance();
        $changes = $changelog->getChanges($location, $data, ['name', 'description']);

        Location::update($id, $data);
        $changelog->logUpdate('location', $id, $data['name'], $changes);

        return ServiceResult::ok(
            ['id' => $id],
            __('flash.updated', ['item' => __('location.title')])
        );
    }

    public function delete(int $id): ServiceResult
    {
        $location = Location::find($id);
        if (!$location) return ServiceResult::fail([], __('location.not_found'));

        ChangelogService::getInstance()->logDelete('location', $id, $location['name'], $location);
        Location::delete($id);

        return ServiceResult::ok(
            ['id' => $id],
            __('flash.deleted', ['item' => __('location.title')])
        );
    }

    private function validate(array $data, ?int $excludeId = null): array
    {
        $validator = Validator::make($data, ['name' => 'required|max:150']);
        return $validator->errors();
    }
}
