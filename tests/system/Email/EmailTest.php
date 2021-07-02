<?php

namespace CodeIgniter\Email;

use CodeIgniter\Events\Events;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockEmail;

/**
 * @internal
 */
final class EmailTest extends CIUnitTestCase
{
    public function testEmailValidation()
    {
        $config           = config('Email');
        $config->validate = true;
        $email            = new Email($config);
        $email->setTo('invalid');
        $this->assertStringContainsString('Invalid email address: invalid', $email->printDebugger());
    }

    public function autoClearProvider()
    {
        return [
            'autoclear'     => [true],
            'not autoclear' => [false],
        ];
    }

    /**
     * @dataProvider autoClearProvider
     */
    public function testEmailSendWithClearance($autoClear)
    {
        $config           = config('Email');
        $config->validate = true;
        $email            = new MockEmail($config);
        $email->setTo('foo@foo.com');

        $this->assertTrue($email->send($autoClear));

        if (! $autoClear) {
            $this->assertSame('foo@foo.com', $email->archive['recipients'][0]);
        }
    }

    public function testEmailSendStoresArchive()
    {
        $config           = config('Email');
        $config->validate = true;
        $email            = new MockEmail($config);
        $email->setTo('foo@foo.com');
        $email->setFrom('bar@foo.com');
        $email->setSubject('Archive Test');

        $this->assertTrue($email->send());

        $this->assertNotEmpty($email->archive);
        $this->assertSame(['foo@foo.com'], $email->archive['recipients']);
        $this->assertSame('bar@foo.com', $email->archive['fromEmail']);
        $this->assertSame('Archive Test', $email->archive['subject']);
    }

    public function testAutoClearLeavesArchive()
    {
        $config           = config('Email');
        $config->validate = true;
        $email            = new MockEmail($config);
        $email->setTo('foo@foo.com');

        $this->assertTrue($email->send(true));

        $this->assertNotEmpty($email->archive);
    }

    public function testEmailSendRepeatUpdatesArchive()
    {
        $config = config('Email');
        $email  = new MockEmail($config);
        $email->setTo('foo@foo.com');
        $email->setFrom('bar@foo.com');

        $this->assertTrue($email->send());

        $email->setFrom('');
        $email->setSubject('Archive Test');
        $this->assertTrue($email->send());

        $this->assertSame('', $email->archive['fromEmail']);
        $this->assertSame('Archive Test', $email->archive['subject']);
    }

    public function testSuccessDoesTriggerEvent()
    {
        $config           = config('Email');
        $config->validate = true;
        $email            = new MockEmail($config);
        $email->setTo('foo@foo.com');

        $result = null;

        Events::on('email', static function ($arg) use (&$result) {
            $result = $arg;
        });

        $this->assertTrue($email->send());

        $this->assertIsArray($result);
        $this->assertSame(['foo@foo.com'], $result['recipients']);
    }

    public function testFailureDoesNotTriggerEvent()
    {
        $config           = config('Email');
        $config->validate = true;
        $email            = new MockEmail($config);
        $email->setTo('foo@foo.com');
        $email->returnValue = false;

        $result = null;

        Events::on('email', static function ($arg) use (&$result) {
            $result = $arg;
        });

        $this->assertFalse($email->send());

        $this->assertNull($result);
    }
}
