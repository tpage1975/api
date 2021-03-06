<?php

namespace Tests\Unit\Console\Commands\Tlr\Notify;

use App\Console\Commands\Tlr\Notify\StaleServicesCommand;
use App\Emails\ServiceUpdatePrompt\NotifyGlobalAdminEmail;
use App\Emails\ServiceUpdatePrompt\NotifyServiceAdminEmail;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class StaleServicesCommandTest extends TestCase
{
    /*
     * 6 to 12 months.
     */
    public function test_6_to_12_months_emails_not_sent_after_5_months()
    {
        Queue::fake();

        $service = factory(Service::class)->create([
            'last_modified_at' => Date::now()->subMonths(5),
        ]);

        $user = factory(User::class)->create();
        $this->makeServiceAdmin($user, $service);

        Artisan::call(StaleServicesCommand::class);

        Queue::assertNotPushed(NotifyServiceAdminEmail::class);
    }

    public function test_6_to_12_months_emails_not_sent_after_13_months()
    {
        Queue::fake();

        $service = factory(Service::class)->create([
            'last_modified_at' => Date::now()->subMonths(13),
        ]);

        $this->makeServiceAdmin(factory(User::class)->create(), $service);

        Artisan::call(StaleServicesCommand::class);

        Queue::assertNotPushed(NotifyServiceAdminEmail::class);
    }

    public function test_6_to_12_months_emails_sent_after_6_months()
    {
        Queue::fake();

        $service = factory(Service::class)->create([
            'last_modified_at' => Date::now()->subMonths(6),
        ]);

        $user = factory(User::class)->create();
        $this->makeServiceAdmin($user, $service);

        Artisan::call(StaleServicesCommand::class);

        Queue::assertPushed(NotifyServiceAdminEmail::class, function (NotifyServiceAdminEmail $email): bool {
            $this->assertArrayHasKey('SERVICE_NAME', $email->values);
            $this->assertArrayHasKey('SERVICE_URL', $email->values);
            $this->assertArrayHasKey('SERVICE_STILL_UP_TO_DATE_URL', $email->values);
            return true;
        });
    }

    public function test_6_to_12_months_emails_sent_after_12_months()
    {
        Queue::fake();
        Date::setTestNow(Date::today());

        $service = factory(Service::class)->create([
            'last_modified_at' => Date::now()->subMonths(12),
        ]);

        $user = factory(User::class)->create();
        $this->makeServiceAdmin($user, $service);

        Artisan::call(StaleServicesCommand::class);

        Queue::assertPushed(NotifyServiceAdminEmail::class, function (NotifyServiceAdminEmail $email): bool {
            $this->assertArrayHasKey('SERVICE_NAME', $email->values);
            $this->assertArrayHasKey('SERVICE_URL', $email->values);
            $this->assertArrayHasKey('SERVICE_STILL_UP_TO_DATE_URL', $email->values);
            return true;
        });
    }

    public function test_6_to_12_months_emails_sent_after_9_months()
    {
        Queue::fake();

        $service = factory(Service::class)->create([
            'last_modified_at' => Date::now()->subMonths(9),
        ]);

        $user = factory(User::class)->create();
        $this->makeServiceAdmin($user, $service);

        Artisan::call(StaleServicesCommand::class);

        Queue::assertPushed(NotifyServiceAdminEmail::class, function (NotifyServiceAdminEmail $email): bool {
            $this->assertArrayHasKey('SERVICE_NAME', $email->values);
            $this->assertArrayHasKey('SERVICE_URL', $email->values);
            $this->assertArrayHasKey('SERVICE_STILL_UP_TO_DATE_URL', $email->values);
            return true;
        });
    }

    public function test_6_to_12_months_emails_not_sent_to_service_workers()
    {
        Queue::fake();

        $service = factory(Service::class)->create([
            'last_modified_at' => Date::now()->subMonths(9),
        ]);

        $user = factory(User::class)->create();
        $this->makeServiceWorker($user, $service);

        Artisan::call(StaleServicesCommand::class);

        Queue::assertNotPushed(NotifyServiceAdminEmail::class);
    }

    public function test_6_to_12_months_emails_not_sent_to_global_admins()
    {
        Queue::fake();

        factory(Service::class)->create([
            'last_modified_at' => Date::now()->subMonths(9),
        ]);

        $user = factory(User::class)->create();
        $this->makeGlobalAdmin($user);

        Artisan::call(StaleServicesCommand::class);

        Queue::assertNotPushed(NotifyServiceAdminEmail::class);
    }

    /*
     * After 12 months.
     */

    public function test_after_12_months_emails_not_sent_after_11_months()
    {
        Queue::fake();

        factory(Service::class)->create([
            'last_modified_at' => Date::now()->subMonths(11),
        ]);

        $user = factory(User::class)->create();
        $this->makeSuperAdmin($user);

        Artisan::call(StaleServicesCommand::class);

        Queue::assertNotPushed(NotifyGlobalAdminEmail::class);
    }

    public function test_after_12_months_emails_not_sent_after_13_months()
    {
        Queue::fake();

        factory(Service::class)->create([
            'last_modified_at' => Date::now()->subMonths(13),
        ]);

        $user = factory(User::class)->create();
        $this->makeSuperAdmin($user);

        Artisan::call(StaleServicesCommand::class);

        Queue::assertNotPushed(NotifyGlobalAdminEmail::class);
    }

    public function test_after_12_months_emails_sent_after_12_months()
    {
        Queue::fake();

        factory(Service::class)->create([
            'last_modified_at' => Date::now()->subMonths(12),
        ]);

        $user = factory(User::class)->create();
        $this->makeSuperAdmin($user);

        Artisan::call(StaleServicesCommand::class);

        Queue::assertPushed(NotifyGlobalAdminEmail::class, function (NotifyGlobalAdminEmail $email): bool {
            $this->assertArrayHasKey('SERVICE_NAME', $email->values);
            $this->assertArrayHasKey('SERVICE_URL', $email->values);
            $this->assertArrayHasKey('SERVICE_ADMIN_NAMES', $email->values);
            $this->assertArrayHasKey('SERVICE_STILL_UP_TO_DATE_URL', $email->values);
            return true;
        });
    }
}
