<?php
use Gwa\Facebook\User;
use Gwa\Filesystem\gwDirectory;

class UserTest extends PHPUnit_Framework_TestCase
{
    const APP_ID       = '383320168496795';
    const APP_SECRET   = '867cb607898cb0b84125df45e3bcf331';
    const ACCESS_TOKEN = 'CAAFcoK56fpsBAKtjx6YaYOmmNd5XpwhqaqU8kY9gIabdXzXaXkpeYcZBq8l6piESoqvdxXZASWtNjGPmsmjv4RZADziNZA5XXOt8RTaA4ufbtTmqg1ucuwFi2sCejkD05mU3qLvF5UrMkZCJuwZAFeoQGsHttf9J9JA89KxzhEKdE7TJKVPPirZB40C2sXXliuOszxZBnG8IR9bqCzy6ZAWxcgpYstJSi5HQZD';

    const TEST_USER_ID = '1386357951666752';

    static public function setUpBeforeClass()
    {
        if (is_dir(__DIR__ . '/images/fb')) {
            $dir = new gwDirectory(__DIR__ . '/images/fb');
            $dir->delete();
        }
    }

    public function testCanBeConstructed()
    {
        $user = new User(self::APP_ID, self::APP_SECRET);
        $this->assertInstanceOf('Gwa\Facebook\User', $user);
    }

    public function testCanSetAccessToken()
    {
        $user = new User(self::APP_ID, self::APP_SECRET);
        $user->setAccessToken(self::ACCESS_TOKEN);
        $me = $user->me();
        $this->assertInstanceOf('Facebook\GraphUser', $me);
        $this->assertEquals(self::TEST_USER_ID, $me->getId());
    }

    public function testCanSavePictureLocally()
    {
        $user = new User(self::APP_ID, self::APP_SECRET);
        $user->savePictureLocally(
            __DIR__ . '/images/fb',
            '562803288'
        );
        $this->assertTrue(file_exists(__DIR__ . '/images/fb/562803288.jpg'));

        $user->savePictureLocally(
            __DIR__ . '/images/fb',
            '562803288',
            'large',
            '562803288_large'
        );
        $this->assertTrue(file_exists(__DIR__ . '/images/fb/562803288_large.jpg'));

        $user->setAccessToken(self::ACCESS_TOKEN);
        $user->savePictureLocally(__DIR__ . '/images/fb');
        $this->assertTrue(file_exists(__DIR__ . '/images/fb/' . self::TEST_USER_ID . '.jpg'));
    }
}
