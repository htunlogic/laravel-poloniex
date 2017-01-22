<?php
namespace Htunlogic\Poloniex;

class Client implements ClientContract
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
     * @param array $urls
     */
    public function __construct(array $auth, array $urls)
    {
        $this->tradingUrl = array_get($urls, 'trading');
        $this->publicUrl = array_get($urls, 'public');

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
            $this->getBalances(), strtoupper($currency)
        );
    }

    /**
     * Get my open orders.
     *
     * @param string $pair
     * @return array
     */
    public function getOpenOrders($pair)
    {
        return $this->trading([
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
        return $this->trading([
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
        return $this->trading([
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
        return $this->trading([
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
     * @return array
     */
    public function getTradeHistory($pair, $start = null, $end = null, $period = null)
    {
        return $this->public(array_merge([
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
     * @return array
     */
    public function getOrderBook($pair, $depth = 10)
    {
        return $this->public([
            'command' => 'returnOrderBook',
            'currencyPair' => strtoupper($pair),
            'depth' => $depth
        ]);
    }

    /**
     * Returns the trading volume.
     *
     * @param string $pair
     * @return array|null
     */
    public function getVolumeFor($pair)
    {
        $pair = strtoupper($pair);

        return array_get($this->getVolume(), $pair);
    }

    /**
     * Get trading pairs.
     *
     * @return array
     */
    public function getTradingPairs()
    {
        return array_keys($this->public([
            'command' => 'returnTicker'
        ]));
    }

    /**
     * Get trading pairs.
     *
     * @param string $pair
     * @return array|null
     */
    public function getTicker($pair)
    {
        $pair = strtoupper($pair);

        return array_get($this->getTickers(), $pair);
    }

    /**
     * @inheritdoc
     */
    public function getBalances()
    {
        return array_map(function ($balance) {
            return (float) $balance;
        }, $this->trading([
            'command' => 'returnBalances'
        ]));
    }

    /**
     * @inheritdoc
     */
    public function getVolume()
    {
        return $this->public([
            'command' => 'return24hVolume'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getTickers()
    {
        return $this->public([
            'command' => 'returnTicker'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function buyOrSell($command, $pair, $rate, $amount, $type = null)
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

        return $this->trading($parameters);
    }

    /**
     * @inheritdoc
     */
    public function formatDates($start = null, $end = null)
    {
        if (is_object($start) && property_exists($start, 'timestamp')) {
            $start = $start->timestamp;
        }
        else if (! is_numeric($start) && ! is_null($start)) {
            $start = strtotime($start);
        }

        if (is_object($end) && property_exists($end, 'timestamp')) {
            $end = $end->timestamp;
        }
        else if (! is_numeric($end) && ! is_null($end)) {
            $end = strtotime($end);
        }

        return [
            'start' => $start,
            'end' => $end
        ];
    }

    /**
     * @inheritdoc
     */
    public function public(array $parameters)
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

        return json_decode($feed, true);
    }

    /**
     * @inheritdoc
     */
    public function trading(array $parameters = [])
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