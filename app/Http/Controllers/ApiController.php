<?php
/**
 * Created by PhpStorm.
 * User: Aram
 * Date: 21/10/2020
 * Time: 15:09
 */

namespace App\Http\Controllers;


class ApiController extends Controller
{
    // majid.akbari@devolon.fi
    public function test()
    {
        $json = file_get_contents(url("test.json"));

        $decoded = [];
        self::myExplodeJson($json, $decoded);
        // $decoded = json_decode($json, true);

        dd($decoded);
    }

    /**
     * @param string $json
     * @param array $decoded
     */
    public static function myExplodeJson(string $json, array &$decoded)
    {
        $json = preg_replace('/\s+/', ' ', trim($json));
        $json = str_replace(["\r\n", "\r", "\n"], "", $json);


        if (strpos(trim($json, '{}'), '{') === false) {
            $decoded = self::myJsonDecode($json);
        } else {
            while (strlen(trim($json, '{}')) > 0) {
                $semicolon = strpos($json, ':');
                $key = substr($json, 0, $semicolon);

                $key = trim($key, ' {"');

                $colon = strpos($json, ',');
                $openingBracket = strpos($json, '{', $semicolon);

                $closingBracket = false;
                $letters = str_split($json);
                $counter = 1;
                // Find the matching closing bracket
                for ($i = $openingBracket + 1; $i < strlen($json); $i++) {
                    if ($letters[$i] == '{') {
                        $counter++;
                    } elseif ($letters[$i] == '}') {
                        $counter--;
                    }
                    if ($counter == 0) {
                        $closingBracket = $i;
                        break;
                    }
                }

                if ($colon) {
                    if ($openingBracket && $openingBracket < $colon) {
                        // there is a nested object
                        $decoded[$key] = [];
                        $subObj = substr($json, $openingBracket, $closingBracket - $openingBracket + 1);
                        $json = '{' . substr($json, $closingBracket + 2);

                        self::myExplodeJson($subObj, $decoded[$key]);
                    } else {
                        // there is no nested object
                        $value = substr($json, $semicolon + 1, $colon - $semicolon);
                        $value = trim($value, ' ",');
                        $decoded[$key] = $value;
                        $json = '{' . substr($json, $colon + 2);
                    }
                }

            }

        }

    }

    public static function myJsonDecode(string $json): array
    {
        $string = trim($json, "{}");
        $exploded = explode(',', $string);
        $decoded = [];
        //dd($exploded);
        foreach ($exploded as $item) {
            $pairExploded = explode(':', $item);
            $key = trim($pairExploded[0], ' "');
            $value = trim($pairExploded[1]);
            if (strpos($value, '{') !== false) {
                dd($key, $value);
                $value = self::myJsonDecode($value);
            }
            if (strpos($value, '"') !== false) {
                $value = trim($value, '"');
            } elseif (strpos($value, 'true') !== false) {
                $value = true;
            } elseif (strpos($value, 'false') !== false) {
                $value = false;
            } elseif (intval($value) == $value) {
                $value = intval($value);
            } else {
                $value = floatval($value);
            }


            $decoded[$key] = $value;
        }
        return $decoded;
    }
}