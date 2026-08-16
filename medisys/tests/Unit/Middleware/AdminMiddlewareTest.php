<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\DoctorMiddleware;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    private function handle(object $middleware, ?User $user)
    {
        $request = Request::create('/api/admin/nurses', 'GET');
        $request->setUserResolver(fn () => $user);

        return $middleware->handle($request, fn () => response('passed'));
    }

    public function test_admin_middleware_allows_admin(): void
    {
        $response = $this->handle(new AdminMiddleware, new User(['role' => 'admin']));

        $this->assertSame('passed', $response->getContent());
    }

    public function test_admin_middleware_rejects_guest(): void
    {
        $response = $this->handle(new AdminMiddleware, null);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame(
            ['success' => false, 'message' => 'Access denied. Admin role required.'],
            $response->getData(true)
        );
    }

    public function test_admin_middleware_rejects_non_admin(): void
    {
        $response = $this->handle(new AdminMiddleware, new User(['role' => 'doctor']));

        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_doctor_middleware_allows_doctor(): void
    {
        $response = $this->handle(new DoctorMiddleware, new User(['role' => 'doctor']));

        $this->assertSame('passed', $response->getContent());
    }

    public function test_doctor_middleware_rejects_other_roles_and_guests(): void
    {
        $rejected = $this->handle(new DoctorMiddleware, new User(['role' => 'admin']));
        $guest = $this->handle(new DoctorMiddleware, null);

        $this->assertSame(403, $rejected->getStatusCode());
        $this->assertSame('Access denied. Doctor role required.', $rejected->getData(true)['message']);
        $this->assertSame(403, $guest->getStatusCode());
    }
}
