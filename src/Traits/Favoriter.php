<?php

/*
 * This file is part of the overtrue/laravel-favorite
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\LaravelFavorite\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Trait Favoriter.
 *
 * @property \Illuminate\Database\Eloquent\Collection $favorites
 */
trait Favoriter
{
    public function favorite(Model $object)
    {
        /* @var \Overtrue\LaravelFavorite\Traits\Favoriter $object */
        if (!$this->hasFavorited($object)) {
            $favorite = app(config('favorite.favorite_model'));
            $favorite->{config('favorite.user_foreign_key')} = $this->getKey();

            $object->favorites()->save($favorite);
        }
    }

    public function unfavorite(Model $object)
    {
        /* @var \Overtrue\LaravelFavorite\Traits\Favoriter $object */
        $relation = $object->favorites()
            ->where('favoriteable_id', $object->getKey())
            ->where('favoriteable_type', $object->getMorphClass())
            ->where(config('favorite.user_foreign_key'), $this->getKey())
            ->first();

        if ($relation) {
            $relation->delete();
        }
    }

    public function toggleFavorite(Model $object)
    {
        $this->hasFavorited($object) ? $this->unfavorite($object) : $this->favorite($object);
    }

    /**
     * @return bool
     */
    public function hasFavorited(Model $object)
    {
        return tap($this->relationLoaded('favorites') ? $this->favorites : $this->favorites())
            ->where('favoriteable_id', $object->getKey())
            ->where('favoriteable_type', $object->getMorphClass())
            ->count() > 0;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favorites()
    {
        return $this->hasMany(config('favorite.favorite_model'), config('favorite.user_foreign_key'), $this->getKeyName());
    }
}
