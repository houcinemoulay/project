<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class SetLocaleTest extends TestCase
{
    private function handle()
    {
        return (new SetLocale)->handle(Request::create('/', 'GET'), fn () => response('passed'));
    }

    public function test_uses_locale_stored_in_session(): void
    {
        Session::put('app_locale', 'fr');

        $response = $this->handle();

        $this->assertSame('fr', App::getLocale());
        $this->assertSame('passed', $response->getContent());
    }

    public function test_falls_back_to_english_without_session_locale(): void
    {
        Session::forget('app_locale');
        App::setLocale('ar');

        $this->handle();

        $this->assertSame('en', App::getLocale());
    }
}
