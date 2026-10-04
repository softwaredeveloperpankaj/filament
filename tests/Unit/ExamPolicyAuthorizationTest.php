<?php

namespace Tests\Unit;

use App\Models\ExamResult;
use App\Models\User;
use App\Policies\ExamResultPolicy;
use Mockery;
use Tests\TestCase;

class ExamPolicyAuthorizationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_admin_can_view_same_branch_result_even_without_explicit_permission(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('branch_id')->andReturn(1);
        $user->shouldReceive('hasAnyRole')->with(['super_admin', 'admin', 'principal'])->andReturn(true);
        $user->shouldReceive('can')->with('View:ExamResult')->andReturn(false);

        $result = new ExamResult();
        $result->setRelation('exam', (object) ['branch_id' => 1]);

        $policy = new ExamResultPolicy();

        $this->assertTrue($policy->view($user, $result));
    }

    public function test_admin_cannot_view_different_branch_result(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('branch_id')->andReturn(1);
        $user->shouldReceive('hasAnyRole')->with(['super_admin', 'admin', 'principal'])->andReturn(true);
        $user->shouldReceive('can')->with('View:ExamResult')->andReturn(false);

        $result = new ExamResult();
        $result->setRelation('exam', (object) ['branch_id' => 2]);

        $policy = new ExamResultPolicy();

        $this->assertFalse($policy->view($user, $result));
    }
}
