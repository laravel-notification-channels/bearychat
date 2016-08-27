<?php

namespace NotificationChannels\BearyChat\Test;

use ElfSundae\BearyChat\Message;

class MessageTest extends TestCase
{
    public function testInstantiation()
    {
        $this->assertInstanceOf(Message::class, $this->getMessage());
    }

    public function testClientShouldBeEmpty()
    {
        $message = $this->getMessage();

        $this->assertNull($message->getClient());
    }

    public function testMessageIsArrayable()
    {
        $message = $this->getMessage();

        $this->assertInternalType('array', $message->toArray());
    }

    public function testCanNotBeSent()
    {
        $message = $this->getMessage();

        $this->assertFalse($message->send());

        $this->assertFalse($message->sendTo('foo'));
    }

    public function testSetText()
    {
        $message = $this->getMessage();

        $message->text('foo');

        $this->assertSame('foo', $message->getText());
    }

    public function testSetNotification()
    {
        $message = $this->getMessage();

        $message->notification('foo');

        $this->assertSame('foo', $message->getNotification());
    }

    public function testSetMarkdown()
    {
        $message = $this->getMessage();

        $message->markdown(false);

        $this->assertFalse($message->getMarkdown());
    }

    public function testSetChannel()
    {
        $message = $this->getMessage();

        $message->channel('foo');

        $this->assertSame('foo', $message->getChannel());
    }

    public function testSetUser()
    {
        $message = $this->getMessage();

        $message->user('foo');

        $this->assertSame('foo', $message->getUser());
    }

    public function testSetToUser()
    {
        $message = $this->getMessage();

        $message->to('@foo');

        $this->assertSame('foo', $message->getUser());
    }

    public function testSetToChannel()
    {
        $message = $this->getMessage();

        $message->to('#foo');

        $this->assertSame('foo', $message->getChannel());
    }

    public function testSetToChannelByDefault()
    {
        $message = $this->getMessage();

        $message->to('foo');

        $this->assertSame('foo', $message->getChannel());

        $this->assertNull($message->getUser());

        $this->assertEquals(['channel' => 'foo'], $message->toArray());
    }

    public function testSetAttachments()
    {
        $message = $this->getMessage();

        $var = [
            [
                'title' => 'foo',
                'text' => 'bar',
            ],
        ];

        $message->attachments($var);

        $this->assertEquals($var, $message->getAttachments());
    }

    public function testAddAttachmentAsArray()
    {
        $message = $this->getMessage();

        $var = [
            'text' => 'foo',
        ];

        $message->add($var);

        $this->assertCount(1, $message->getAttachments());

        $this->assertEquals($var, $message->getAttachments()[0]);
    }

    public function testAddAttachmentAsVariableArguments()
    {
        $message = $this->getMessage();

        $message->add('foo', 'bar', 'http://image.url', 'red');

        $this->assertCount(1, $message->getAttachments());

        $this->assertEquals([
            'text' => 'foo',
            'title' => 'bar',
            'images' => [
                ['url' => 'http://image.url'],
            ],
            'color' => 'red',
        ],
            $message->getAttachments()[0]
        );
    }

    public function testAddingAttachmentFailed()
    {
        $message = $this->getMessage();

        $message->add(null);

        $this->assertEmpty($message->toArray());
    }

    public function testRemoveAttachment()
    {
        $message = $this->getMessage();

        $message->add('foo', 'bar');

        $this->assertCount(1, $message->getAttachments());

        $message->remove();

        $this->assertCount(0, $message->getAttachments());
    }

    protected function getMessage()
    {
        return new Message();
    }
}
