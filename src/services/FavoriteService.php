<?php
declare(strict_types=1);

class FavoriteService
{
    public function toggleGame(int $gameId): ServiceResult
    {
        $game = Game::find($gameId);
        if (!$game) return ServiceResult::fail([], __('game.not_found'));

        $isFavorite = Game::toggleFavorite($gameId);
        return ServiceResult::ok(['is_favorite' => $isFavorite]);
    }

    public function toggleMaterial(int $materialId): ServiceResult
    {
        $material = Material::find($materialId);
        if (!$material) return ServiceResult::fail([], __('material.not_found'));

        $isFavorite = Material::toggleFavorite($materialId);
        return ServiceResult::ok(['is_favorite' => $isFavorite]);
    }
}
