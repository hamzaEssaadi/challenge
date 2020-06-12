<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class APIController extends Controller
{
    //list of the languages used by the 100 trending public repos on GitHub
    function listLanguages(){
        //Get the Date of the last month
        $lastMonthDate=Carbon::now()->subMonth()->format('Y-m-d');

        // Getting the data of repos by calling http request
        $url="https://api.github.com/search/repositories?q=created:>$lastMonthDate&sort=stars&order=desc&per_page=100";
        $result=$this->callAPi($url,'GET');
        $listOfRepos=collect($result->items);

        // getting the language list
        $languageList= $listOfRepos->unique(['language'])->pluck('language');

        // Get the number of repos per language
        $numberOfReposPerLanguage=$listOfRepos->countBy(function ($repo){
            return $repo->language;
        });

         $result=array();

         // building thz result for each language which contains:
          //      -number of  repos of each language
          //      -list of repos of each language
         foreach ($languageList as $lang){
             $info=array(
                 "language"=>$lang,
                 "number_of_repos"=>$numberOfReposPerLanguage->get($lang),
                 "list_of_repos"=>$listOfRepos->filter(function ($rep) use($lang){
                     return ($lang==$rep->language);
                 })->pluck('name')
             );
             $result[]=$info;
         }
         // return the response
        return response()->json($result);
    }

    function callAPi($url,$method){

        $client=new Client(['verify' => false ]);
        try {
            $res = $client->request($method, $url);
        } catch (GuzzleException $e) {
            return $e->getMessage();
        }
        $result=json_decode($res->getBody()->getContents());
        return $result;
    }
}
