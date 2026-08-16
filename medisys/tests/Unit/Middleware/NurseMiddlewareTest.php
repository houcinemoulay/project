<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\NurseMiddleware;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class NurseMiddlewareTest extends TestCase
{
    private function handle(?User $user, bool $expectsJson = false)
    {
        $request = Request::create('/nurse/dashboard', 'GET');

        if ($expectsJson) {
            $request->headers->set('Accept', 'application/json');
        }

        $request->setUserResolver(fn () => $user);

        return (new NurseMiddleware)->handle($request, fn () => response('passed'));
    }

    public function test_allows_nurse(): void
    {
        $this->assertSame('passed', $this->handle(new User(['role' => 'nurse']))->getContent());
    }

    public function test_allows_admin(): void
    {
        $this->assertSame('passed', $this->handle(new User(['role' => 'admin']))->getContent());
    }

    public function test_returns_401_json_for_guest_api_request(): void
    {
        $response = $this->handle(null, expectsJson: true);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('Unauthenticated.', $response->getData(true)['message']);
    }

    public function test_redirects_guest_web_request_to_login(): void
    {
        $response = $this->handle(null);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(route('login'), $response->getTargetUrl());
    }

    public function test_returns_403_json_for_other_roles(): void
    {
        $response = $this->handle(new User(['role' => 'doctor']), expectsJson: true);

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('Access denied. Nurse or admin role required.', $response->getData(true)['message']);
    }

    public function test_aborts_with_403_for_other_roles_on_web_request(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Access denied. Nurse or admin role required.');

        $this->handle(new User(['role' => 'pharmacy']));
    }
}
