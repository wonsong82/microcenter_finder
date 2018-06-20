<?php
namespace App\Pkg;

use App\OpenboxItem;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Mail;

class Microcenter {

    protected $client;
    protected $searchUrl;
    protected $storeNumber;


    public function __construct()
    {
        $this->searchUrl = 'http://www.microcenter.com/search/search_results.aspx?Ntk=all&sortby=match&prt=clearance&N=4294967292+4294938195&myStore=true';
        $this->storeNumber = '075';
    }


    public function find()
    {
        // start client
        $cookies = CookieJar::fromArray([
            'storeSelected' => $this->storeNumber
        ], '.microcenter.com');

        $this->client = new Client([
            'cookies' => $cookies
        ]);


        // get list page content
        $listPage = $this->getHttpContent($this->searchUrl);


        // find products
        $regex = '#<li class="product_wrapper">(.+?)</li>#';
        if(false === $products = $this->strMatchAll($regex, $listPage)) exit('no products found');



        foreach($products[1] as $product){

            // find detail page link
            $regex = '#<h2>(.+?)</h2>#';
            if(false === $h2 = $this->strMatch($regex, $product)) continue;

            $regex = '#href="(.+?)"#';
            if(false === $link = $this->strMatch($regex, $h2[1])) continue;

            $title = trim(strip_tags($h2[1]));
            $link = 'http://www.microcenter.com' . $link[1];


            // go to detail page
            $detailPage = $this->getHttpContent($link);

            // items
            $items = $this->parseOpenboxItems($detailPage);

            // other info
            $info = $this->parseOtherInfo($detailPage);
            $info->originalPrice = $this->parseOriginalPrice($detailPage);



            foreach($items as $openboxItem){

                // Check if existing
                $item = OpenboxItem::where('openbox_id', $openboxItem->id)->first();
                if($item){

                    $item->fill([
                        'openbox_price' => (float)$openboxItem->price
                    ]);

                    if($item->isDirty()){
                        $item->save();

                        // send email
                        $this->sendMail($item, 'Item price has been updated');
                    }
                }
                else {
                    $item = OpenboxItem::create([
                        'product_description' => $title,
                        'product_id' => $info->productID,
                        'sku' => $info->SKU,
                        'product_link' => $link,
                        'product_price' => $info->productPrice,
                        'product_original_price' => $info->originalPrice,
                        'openbox_id' => $openboxItem->id,
                        'openbox_price' => $openboxItem->price,
                        'store_number' => $info->storeNum,
                        'mpn' => $info->mpn,
                        'brand' => $info->brand
                    ]);

                    // send email
                    $this->sendMail($item, 'New item added');

                }
            }


        }
    }


    private function sendMail($item, $title)
    {
        //return view('mails.openbox', compact('item', 'title'));

        Mail::send('mails.openbox',compact('item', 'title'),function($m) use ($title, $item){
            $m->from('microcenter@wonsong.com', 'Microcenter Openbox');
            $m->to('wonsong82@gmail.com', 'Won Song');
            $m->cc('annako4u@gmail.com', 'Anna Ko');
            $m->subject('Microcenter Openbox: ' . $title . ': ' . $item->openbox_id);
        });

    }







    private function parseOriginalPrice($detailPage)
    {
        $regex = '#<div class="savings"><span>(.+?)</span>#';
        $originalPrice = $this->strMatch($regex, $detailPage, 1);

        if($originalPrice){
            return trim(preg_replace('#[$,]#', '', $originalPrice));
        }
        else
            return null;

    }


    private function parseOpenboxItems($detailPage)
    {
        // get openbox tab content

        $regex = '#<div id="tab-clearance" class="rounded hide" role="tabpanel">(.+?)</div> <div id="tab-rebate"#';
        $openboxTab = $this->strMatch($regex, $detailPage, 1);
        if(!$openboxTab) return false;


        // get TRs

        $regex = '#<tr class="clearance-body">(.+?)</tr>#';
        $rows = $this->strMatchAll($regex, $openboxTab, 1);
        if(!$rows) return false;


        // get id and price

        $items = [];
        foreach($rows as $row){

            $regex = '#<span class="descriptor">ID: ([\d]+)</span>#';
            $id = $this->strMatch($regex, $row, 1);

            $regex = '#data-price="([\d\.]+)"#';
            $price = $this->strMatch($regex, $row, 1);

            if(!$price || !$id) continue;


            $item = new \stdClass();
            $item->id = $id;
            $item->price = $price;

            $items[] = $item;
        }


        return $items;
    }


    private function parseOtherInfo($detailPage)
    {
        $regex = '#dataLayer = \[\{(.+?)\}\];#';
        $dataLayer = $this->strMatch($regex, $detailPage, 1);

        $info = json_decode('{' . str_replace("'", '"', $dataLayer) . '}');

        return $info;

    }














    protected function getHttpContent($url, $method='GET', $params=[])
    {
        $response = $this->client->request($method, $url, ['form_params' => $params]);
        $content = $response->getBody()->getContents();

        return $this->trimDocument($content);
    }



    protected function trimDocument($httpDoc)
    {
        $doc = preg_replace('#[\n\r\t]#', '', $httpDoc);
        $doc = preg_replace('#[\s]{2,}#', ' ', $doc);

        return $doc;
    }


    protected function strMatch($pattern, $string, $matchNum=null)
    {
        $match = null;
        if(preg_match($pattern, $string, $match)){
            if($matchNum===null)
                return $match;
            else
                return $match[$matchNum];
        }

        return false;
    }

    protected function strMatchAll($pattern, $string, $matchNum=null)
    {
        $match = null;
        if(preg_match_all($pattern, $string, $match)){
            if($matchNum===null)
                return $match;
            else
                return $match[$matchNum];
        }

        return false;
    }


}