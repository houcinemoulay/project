<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\RoleMiddleware;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    private function handle(Request $request, string $role = 'admin')
    {
        return (new RoleMiddleware)->handle(
            $request,
            fn () => response('passed'),
            $role
        );
    }

    private function request(?User $user, bool $expectsJson = false): Request
    {
        $request = Request::create('/admin/nurses', 'GET');

        if ($expectsJson) {
            $request->headers->set('Accept', 'application/json');
        }

        $request->setUserResolver(fn () => $user);

        return $request;
    }

    public function test_passes_request_through_for_matching_role(): void
    {
        $response = $this->handle($this->request(new User(['role' => 'admin'])));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('passed', $response->getContent());
    }

    public function test_returns_401_json_for_guest_api_request(): void
    {
        $response = $this->handle($this->request(null, expectsJson: true));

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame(
            ['success' => false, 'message' => 'Unauthenticated.'],
            $response->getData(true)
        );
    }

    public function test_redirects_guest_web_request_to_login(): void
    {
        $response = $this->handle($this->request(null));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(route('login'), $response->getTargetUrl());
    }

    public function test_returns_403_json_for_wrong_role_api_request(): void
    {
        $response = $this->handle($this->request(new User(['role' => 'doctor']), expectsJson: true));

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('Access denied. Required role: admin.', $response->getData(true)['message']);
    }

    public function test_aborts_with_403_for_wrong_role_web_request(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Access denied. Required role: doctor.');

        $this->handle($this->request(new User(['role' => 'nurse'])), 'doctor');
    }
}
