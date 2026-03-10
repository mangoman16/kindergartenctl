<?php
declare(strict_types=1);

class TagService
{
    public function list(): ServiceResult
    {
        $tags = Tag::allWithGameCount('name', 'ASC');
        return ServiceResult::ok(['tags' => $tags]);
    }

    public function get(int $id): ServiceResult
    {
        $tag = Tag::findWithGameCount($id);
        if (!$tag) return ServiceResult::fail([], __('tag.not_found'));
        return ServiceResult::ok(['tag' => $tag]);
    }

    public function create(array $data): ServiceResult
    {
        $errors = $this->validate($data);
        if (!empty($errors)) return ServiceResult::fail($errors);

        if (!empty($data['name']) && Tag::nameExists($data['name'])) {
            return ServiceResult::fail(['name' => [__('validation.duplicate')]]);
        }

        $tagId = Tag::create($data);
        if (!$tagId) return ServiceResult::fail([], __('flash.error_generic'));

        ChangelogService::getInstance()->logCreate('tag', $tagId, $data['name'], $data);

        return ServiceResult::ok(
            ['id' => $tagId],
            __('flash.created', ['item' => __('tag.title')])
        );
    }

    public function update(int $id, array $data): ServiceResult
    {
        $tag = Tag::find($id);
        if (!$tag) return ServiceResult::fail([], __('tag.not_found'));

        $errors = $this->validate($data, $id);
        if (!empty($errors)) return ServiceResult::fail($errors);

        if (!empty($data['name']) && Tag::nameExists($data['name'], $id)) {
            return ServiceResult::fail(['name' => [__('validation.duplicate')]]);
        }

        $changelog = ChangelogService::getInstance();
        $changes = $changelog->getChanges($tag, $data, ['name', 'description', 'color', 'image_path']);

        Tag::update($id, $data);
        $changelog->logUpdate('tag', $id, $data['name'], $changes);

        return ServiceResult::ok(
            ['id' => $id],
            __('flash.updated', ['item' => __('tag.title')])
        );
    }

    public function delete(int $id): ServiceResult
    {
        $tag = Tag::find($id);
        if (!$tag) return ServiceResult::fail([], __('tag.not_found'));

        ChangelogService::getInstance()->logDelete('tag', $id, $tag['name'], $tag);
        Tag::delete($id);

        if ($tag['image_path']) {
            (new ImageProcessor())->delete($tag['image_path']);
        }

        return ServiceResult::ok(
            ['id' => $id],
            __('flash.deleted', ['item' => __('tag.title')])
        );
    }

    public function quickCreate(string $name): ServiceResult
    {
        if (empty($name)) return ServiceResult::fail(['name' => [__('validation.name_required')]]);
        if (mb_strlen($name) > 100) return ServiceResult::fail(['name' => [__('validation.name_max_100')]]);
        if (Tag::nameExists($name)) return ServiceResult::fail(['name' => [__('validation.duplicate')]]);

        $tagId = Tag::quickCreate($name);
        if (!$tagId) return ServiceResult::fail([], __('flash.error_creating'));

        ChangelogService::getInstance()->logCreate('tag', $tagId, $name, ['name' => $name]);

        return ServiceResult::ok(
            ['id' => $tagId, 'name' => $name, 'color' => null],
            __('flash.created', ['item' => __('tag.title')])
        );
    }

    private function validate(array $data, ?int $excludeId = null): array
    {
        $validator = Validator::make($data, ['name' => 'required|max:100']);
        return $validator->errors();
    }
}
