<?php

use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Talmp\Phputils\EncodeUtil;

class EncodeUtilTest extends TestCase
{
    public function test_base64_encode_decode_url(): void
    {
        $string = bin2hex(openssl_random_pseudo_bytes(rand(100, 200)));

        $encoded_str = EncodeUtil::base64EncodeUrl($string);

        $this->assertFalse(str_contains($encoded_str, '+'));
        $this->assertFalse(str_contains($encoded_str, '/'));
        $this->assertFalse(str_contains($encoded_str, '='));

        $decoded_str = EncodeUtil::base64DecodeUrl($encoded_str);

        $this->assertEquals($string, $decoded_str);
    }

    public function test_gzip_file(): void
    {
        $faker = Faker\Factory::create();

        $text = '';

        $rand = rand(10, 40);

        while ($rand) {
            $rand -= 1;

            $text .= $faker->paragraph()."\n";
        }

        $file_name = bin2hex(random_bytes(10));
        $file_path = sys_get_temp_dir().'/'.$file_name;

        $this->assertFalse(file_exists($file_path));

        $touch = touch($file_path);

        $this->assertTrue($touch);

        $fpc = file_put_contents($file_path, $text);

        $this->assertTrue($fpc !== false);

        $dest_path = EncodeUtil::gzipFile($file_path);

        $this->assertEquals($dest_path, $file_path.'.gz');

        $this->assertTrue(file_exists($dest_path));

        $this->assertGreaterThan(filesize($dest_path), filesize($file_path));

        // test custom dest_path
        $custom_dest_path = sys_get_temp_dir().'/'.bin2hex(random_bytes(10));

        $result_dest_path = EncodeUtil::gzipFile($file_path, $custom_dest_path);

        $this->assertEquals($custom_dest_path, $result_dest_path);

        // clean up
        $this->assertTrue(unlink($file_path));
        $this->assertTrue(unlink($dest_path));
        $this->assertTrue(unlink($custom_dest_path));
    }
}
