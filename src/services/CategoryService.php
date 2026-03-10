<?php
declare(strict_types=1);

class CategoryService
{
    public function list(): ServiceResult
    {
        $categories = Category::allWithGameCount('sort_order', 'ASC');
        return ServiceResult::ok(['categories' => $categories]);
    }

    public function get(int $id): ServiceResult
    {
        $category = Category::findWithGameCount($id);
        if (!$category) return ServiceResult::fail([], __('category.not_found'));
        return ServiceResult::ok(['category' => $category]);
    }

    public function create(array $data): ServiceResult
    {
        $errors = $this->validate($data);
        if (!empty($errors)) return ServiceResult::fail($errors);

        if (!empty($data['name']) && Category::nameExists($data['name'])) {
            return ServiceResult::fail(['name' => [__('validation.duplicate')]]);
        }

        $categoryId = Category::create($data);
        if (!$categoryId) return ServiceResult::fail([], __('flash.error_generic'));

        ChangelogService::getInstance()->logCreate('category', $categoryId, $data['name'], $data);

        return ServiceResult::ok(
            ['id' => $categoryId],
            __('flash.created', ['item' => __('category.title')])
        );
    }

    public function update(int $id, array $data): ServiceResult
    {
        $category = Category::find($id);
        if (!$category) return ServiceResult::fail([], __('category.not_found'));

        $errors = $this->validate($data, $id);
        if (!empty($errors)) return ServiceResult::fail($errors);

        if (!empty($data['name']) && Category::nameExists($data['name'], $id)) {
            return ServiceResult::fail(['name' => [__('validation.duplicate')]]);
        }

        $changelog = ChangelogService::getInstance();
        $changes = $changelog->getChanges($category, $data, ['name', 'description', 'sort_order', 'image_path']);

        Category::update($id, $data);
        $changelog->logUpdate('category', $id, $data['name'], $changes);

        return ServiceResult::ok(
            ['id' => $id],
            __('flash.updated', ['item' => __('category.title')])
        );
    }

    public function delete(int $id): ServiceResult
    {
        $category = Category::find($id);
        if (!$category) return ServiceResult::fail([], __('category.not_found'));

        ChangelogService::getInstance()->logDelete('category', $id, $category['name'], $category);
        Category::delete($id);

        if ($category['image_path']) {
            (new ImageProcessor())->delete($category['image_path']);
        }

        return ServiceResult::ok(
            ['id' => $id],
            __('flash.deleted', ['item' => __('category.title')])
        );
    }

    private function validate(array $data, ?int $excludeId = null): array
    {
        $validator = Validator::make($data, ['name' => 'required|max:50']);
        return $validator->errors();
    }
}
