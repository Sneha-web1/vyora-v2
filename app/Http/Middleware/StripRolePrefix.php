<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role panels live under their own URL prefix:
 *      /test/login               -> login page of role "Test"
 *      /test/master/company      -> the normal  /master/company  page, opened inside the "Test" panel
 *
 * This runs BEFORE routing: it removes the "/{role-slug}" part so the normal routes match,
 * and remembers the slug (request attribute `role_prefix`) so links / redirects keep the prefix.
 */
class StripRolePrefix
{
    public const CACHE_KEY = 'role.login.slugs';

    public function handle(Request $request, Closure $next): Response
    {
        $segments = explode('/', trim($request->path(), '/'));
        $first = $segments[0] ?? '';

        if ($first !== '' && count($segments) >= 1 && isset($this->slugs()[$first])) {
            $second = $segments[1] ?? null;

            // /test/login stays the role login page
            if ($second !== 'login') {
                $rest = array_slice($segments, 1);
                $newPath = '/' . ($rest ? implode('/', $rest) : 'home');
                $qs = $request->server->get('QUERY_STRING');
                $uri = $newPath . ($qs ? '?' . $qs : '');

                (function () use ($uri) {
                    $this->server->set('REQUEST_URI', $uri);
                    $this->pathInfo = null;
                    $this->requestUri = null;
                    $this->baseUrl = null;
                    $this->basePath = null;
                })->call($request);

                $request->attributes->set('role_prefix', $first);
            }
        }

        return $next($request);
    }

    /** [slug => role id] of every custom role (cached, cleared whenever a role changes) */
    protected function slugs(): array
    {
        try {
            return Cache::remember(self::CACHE_KEY, 3600, function () {
                return Role::query()->get()
                    ->reject(fn (Role $r) => $r->isSystem())
                    ->mapWithKeys(fn (Role $r) => [$r->loginSlug() => $r->id])
                    ->all();
            });
        } catch (\Throwable $e) {
            return [];
        }
    }
}
