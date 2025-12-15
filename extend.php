<?php

use Flarum\Extend;
use Flarum\Api\Serializer\PostSerializer;

return [
	(new \Flarum\Extend\Frontend('admin'))
    ->css(__DIR__ . '/admin.css'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    (new Extend\ApiSerializer(PostSerializer::class))
        ->attribute('contentHtml', function (PostSerializer $serializer, $post, array $attributes) {
            $actor = $serializer->getActor();
            $lockedHtml = '<div class="UpvoteLock"><p>🔒 <strong>Locked content</strong></p><p>Upvote this post to unlock it.</p></div>';

            $lockTagSlug = trim((string) resolve('flarum.settings')->get('darkfoxdeveloper-vote-to-see.lock_tag_slug', ''));
            if ($lockTagSlug === '') {
                return $attributes['contentHtml'] ?? null;
            }

            $discussion = $post->discussion ?? null;
            if (!$discussion) {
                return $attributes['contentHtml'] ?? null;
            }

            try {
                $discussion->loadMissing('tags');
            } catch (\Throwable $e) {
                return $attributes['contentHtml'] ?? null;
            }

            $isVoteLocked = false;
            foreach ($discussion->tags ?? [] as $tag) {
                if (($tag->slug ?? null) === $lockTagSlug) {
                    $isVoteLocked = true;
                    break;
                }
            }

            if (!$isVoteLocked) {
                return $attributes['contentHtml'] ?? null;
            }

            if ($actor->isGuest()) {
                return $lockedHtml;
            }

            if ($actor->id === $post->user_id || $actor->isAdmin() || $actor->hasPermission('moderate')) {
                return $attributes['contentHtml'] ?? null;
            }

            $prefix = resolve('flarum.config')->offsetGet('database.prefix') ?? '';
            $table  = $prefix . 'post_votes';
            $db = resolve('db');
            $schema = $db->getSchemaBuilder();

            if (!$schema->hasTable($table)) {
                return $attributes['contentHtml'] ?? null;
            }

            if ($schema->hasColumn($table, 'value')) {
                $hasUpvoted = $db->table($table)
                    ->where('post_id', $post->id)
                    ->where('user_id', $actor->id)
                    ->where('value', 1)
                    ->exists();

                return $hasUpvoted ? ($attributes['contentHtml'] ?? null) : $lockedHtml;
            }

            if ($schema->hasColumn($table, 'type')) {
                $hasUpvoted = $db->table($table)
                    ->where('post_id', $post->id)
                    ->where('user_id', $actor->id)
                    ->where('type', 'up')
                    ->exists();

                return $hasUpvoted ? ($attributes['contentHtml'] ?? null) : $lockedHtml;
            }

            return $attributes['contentHtml'] ?? null;
        }),
];
