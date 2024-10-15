<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;

class WeatherController extends Controller
{
    public function getWeather(Request $request) 
    {
        $lat = $request->input('lat'); // 緯度
        $lng = $request->input('lng'); // 経度 
        // キャッシュパスティング...cacheを更新するフラグ
        $forceUpdate = $request->input('force_update', true); //開発中につきtrue

        // Cache keyを緯度、経度に基づいて設定
        $cacheKey = "weather_{$lat}_{$lng}";

        // force_updateがtrue、又はcacheが存在しない場合APIを呼び出す
        if ( $forceUpdate || !Cache::has($cacheKey) ){
            try{
                $apiKey = env('WEATHER_API_KEY');
    
                /*  lat:    緯度
                    lon:    経度
                    units:  天気データの単位を指定*/
                $url = "http://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lng}&appid={$apiKey}&units=metric";
                
                // 天気情報を取得
                $response = Http::get($url);
                $weatherData = $response->json();
        
                // 必要データのみ抽出（位置、地名、気温、天気、説明、アイコン）
                $data = [
                    'coord'         => $weatherData['coord'],
                    'location'      => $weatherData['name'],
                    'temperature'   => $weatherData['main']['temp'],
                    'weather'       => $weatherData['weather'][0]['main'],
                    'desctiptoin'   => $weatherData['weather'][0]['description'],
                    'icon'          => $weatherData['weather'][0]['icon'],
                ];
    
                // cacheに保存（10min）
                Cache::put($cacheKey, $data, now()->addMinutes(10));
                return response()->json($data);
    
            }catch(\Exception){
                return response()->json(['error' => 'エラーが発生しました。'], 500);
            }

        // キャッシュが存在する場合はそれを返す
        }else{
            return response()->json(Cache::get($cacheKey));
        }
    }
}