<?php
namespace Htunlogic\Poloniex;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class Client
{
    /**
     * @var string
     */
    public $tradingUrl;

    /**
     * @var string
     */
    public $publicUrl;

    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $secret;

    /**
     * Client constructor.
     *
     * @param array $auth
     */
    public function __construct(array $auth)
    {
        $this->tradingUrl = config('poloniex.trading_url');
        $this->publicUrl = config('poloniex.public_url');

        $this->key = array_get($auth, 'key');
        $this->secret = array_get($auth, 'secret');
    }

    /**
     * Get my balances.
     *
     * @return float
     */
    public function getBalanceFor($currency)
    {
        return array_get(
            $this->getBalances()->toArray(), strtoupper($currency)
        );
    }

    /**
     * Get my balances.
     *
     * @return Collection
     */
    public function getBalances()
    {
        $balances = new Collection($this->query([
            'command' => 'returnBalances'
        ]));

        return $balances->map(function ($balance) {
            return (float) $balance;
        });
    }

    /**
     * Get my open orders.
     *
     * @param string $pair
     * @return mixed
     */
    public function getOpenOrders($pair)
    {
        return $this->query([
            'command' => 'returnOpenOrders',
            'currencyPair' => strtoupper($pair)
        ]);
    }

    /**
     * Get my trade history.
     *
     * @param string $pair
     * @return mixed
     */
    public function getMyTradeHistory($pair)
    {
        return $this->query([
            'command' => 'returnTradeHistory',
            'currencyPair' => strtoupper($pair)
        ]);
    }

    /**
     * Buy pair at rate.
     *
     * @param string      $pair
     * @param float       $rate
     * @param float       $amount
     * @param string|null $type
     * @return array
     */
    public function buy($pair, $rate, $amount, $type = null)
    {
        return $this->buyOrSell('buy', $pair, $rate, $amount, $type);
    }

    /**
     * Sell pair at rate.
     *
     * @param string      $pair
     * @param float       $rate
     * @param float       $amount
     * @param string|null $type
     * @return array
     */
    public function sell($pair, $rate, $amount, $type = null)
    {
        return $this->buyOrSell('sell', $pair, $rate, $amount, $type);
    }

    /**
     * Cancel order on a pair by its id.
     *
     * @param string $pair
     * @param int    $id
     * @return mixed
     */
    public function cancelOrder($pair, $id)
    {
        return $this->query([
            'command' => 'cancelOrder',
            'currencyPair' => strtoupper($pair),
            'orderNumber' => $id
        ]);
    }

    /**
     * Withdraw the currency amount to address.
     *
     * @param string $currency
     * @param string $amount
     * @param string $address
     * @return mixed
     */
    public function withdraw($currency, $amount, $address)
    {
        return $this->query([
            'command' => 'withdraw',
            'currency' => strtoupper($currency),
            'amount' => $amount,
            'address' => $address
        ]);
    }

    /**
     * Trade history for given currency pair.
     *
     * @param string      $pair
     * @param string|null $start
     * @param string|null $end
     * @param string|null $period
     * @return Collection
     */
    public function getTradeHistory($pair, $start = null, $end = null, $period = null)
    {
        return $this->retrieve(array_merge([
            'command' => 'returnTradeHistory',
            'currencyPair' => strtoupper($pair),
            'period' => $period
        ], $this->formatDates($start, $end)));
    }

    /**
     * Order book for given currency pair.
     *
     * @param string $pair
     * @param int    $depth
     * @return Collection
     */
    public function getOrderBook($pair, $depth = 10)
    {
        return $this->retrieve([
            'command' => 'returnOrderBook',
            'currencyPair' => strtoupper($pair),
            'depth' => $depth
        ]);
    }

    /**
     * Returns the trading volume.
     *
     * @param string $pair
     * @return array
     */
    public function getVolumeFor($pair)
    {
        $pair = strtoupper($pair);

        return $this->getVolume()->first(function ($p, $key) use ($pair) {
            return $key == $pair;
        });
    }

    /**
     * Returns the trading volume.
     *
     * @return Collection
     */
    public function getVolume()
    {
        return $this->retrieve([
            'command' => 'return24hVolume'
        ]);
    }

    /**
     * Get trading pairs.
     *
     * @return array
     */
    public function getTicker($pair)
    {
        $pair = strtoupper($pair);

        return $this->getTickers()->first(function ($ticker, $key) use ($pair) {
            return $key == $pair;
        });
    }

    /**
     * Returning all pairs with their prices.
     *
     * @return Collection
     */
    public function getTickers()
    {
        return $this->retrieve([
            'command' => 'returnTicker'
        ]);
    }

    /**
     * Get trading pairs.
     *
     * @return Collection
     */
    public function getTradingPairs()
    {
        return $this->retrieve([
            'command' => 'returnTicker'
        ])->keys();
    }

    /**
     * Buy or sell action.
     *
     * @param $pair
     * @param $rate
     * @param $amount
     * @return array
     */
    protected function buyOrSell($command, $pair, $rate, $amount, $type = null)
    {
        $parameters = [
            'command' => $command,
            'currencyPair' => strtoupper($pair),
            'rate' => $rate,
            'amount' => $amount
        ];

        if ($type == 'fillOrKill') {
            $parameters['fillOrKill'] = 1;
        }
        else if ($type == 'immediateOrCancel') {
            $parameters['immediateOrCancel'] = 1;
        }
        else if ($type == 'postOnly') {
            $parameters['postOnly'] = 1;
        }

        return $this->query($parameters);
    }

    /**
     * Format the dates into numeric timestamps.
     *
     * @param mixed $start
     * @param mixed $end
     * @return array
     */
    protected function formatDates($start = null, $end = null)
    {
        if ($start instanceof Carbon) {
            $start = $start->timestamp;
        }
        else if (! is_numeric($start) && ! is_null($start)) {
            $start = strtotime($start);
        }

        if ($end instanceof Carbon) {
            $end = $end->timestamp;
        }
        else if (! is_numeric($end) && ! is_null($start)) {
            $end = strtotime($end);
        }

        return [
            'start' => $start,
            'end' => $end
        ];
    }

    /**
     * Make request on public API.
     *
     * @param array $parameters
     * @return Collection
     */
    private function retrieve(array $parameters)
    {
        $options = [
            'http' => [
                'method'  => 'GET',
                'timeout' => 10
            ]
        ];

        $url = $this->publicUrl . '?' . http_build_query(array_filter($parameters));

        $feed = file_get_contents(
            $url, false, stream_context_create($options)
        );

        return new Collection(
            json_decode($feed, true)
        );
    }

    /**
     * Query the trading Api
     *
     * @param array $parameters
     * @return mixed
     */
    private function query(array $parameters = [])
    {
        $mt = explode(' ', microtime());
        $parameters['nonce'] = $mt[1].substr($mt[0], 2, 6);

        $post = http_build_query(array_filter($parameters), '', '&');
        $sign = hash_hmac('sha512', $post, $this->secret);

        $headers = [
            'Key: '.$this->key,
            'Sign: '.$sign,
        ];

        static $ch = null;

        if (is_null($ch)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT,
                'Mozilla/4.0 (compatible; Poloniex PHP-Laravel Client; '.php_uname('a').'; PHP/'.phpversion().')'
            );
        }

        curl_setopt($ch, CURLOPT_URL, $this->tradingUrl);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);

        if ($response === false) {
            throw new Exception('Curl error: '.curl_error($ch));
        }

        return json_decode($response, true);
    }
}