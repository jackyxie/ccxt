<?php

namespace ccxt;

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

use Exception as Exception; // a common import

class bitforex extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'bitforex',
            'name' => 'BitForex',
            'countries' => 'SG',
            'version' => 'v1',
            'has' => array (
                'fetchTickers' => true,
                'fetchOpenOrders' => true,
                'fetchOrders' => true,
                'fetchMyTrades' => true,
            ),
            'urls' => array (
                'logo' => '',
                'api' => 'https://api.bitforex.com/api',
                'www' => 'https://www.bitforex.com',
                'doc' => 'https://github.com/bitforexapi/API_Docs/wiki',
                'fees' => 'https://support.bitforex.com/hc/en-us/articles/360006824872-Trading-Fees',
                'referral' => 'https://www.bitforex.com',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'market/symbols',
                        'market/ticker',
                        'market/depth',
                        'market/trades',
                        'market/kline',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'fund/mainAccount',
                        'fund/allAccount',
                        'trade/placeOrder',
                        'trade/placeMultiOrder',
                        'trade/cancelOrder',
                        'trade/cancelMultiOrder',
                        'trade/cancelAllOrder',
                        'trade/orderInfo',
                        'trade/multiOrderInfo',
                        'trade/orderInfos',
                    ),
                ),
            ),
            'exceptions' => array (
                // see https://github.com/bitforexapi/API_Docs/wiki/%E5%BC%82%E5%B8%B8%E4%BB%A3%E7%A0%81%E8%AF%B4%E6%98%8E
                '1000' => '\\ccxt\\ExchangeNotAvailable', // "syntax error"
                '1001' => '\\ccxt\\ExchangeNotAvailable',
                '1002' => '\\ccxt\\ExchangeError',
                '1003' => '\\ccxt\\ExchangeError',
                '1010' => '\\ccxt\\ExchangeNotAvailable',
                '1011' => '\\ccxt\\AuthenticationError',
                '1012' => '\\ccxt\\InvalidNonce',
                '1013' => '\\ccxt\\AuthenticationError',
                '1014' => '\\ccxt\\ExchangeError',
                '1015' => '\\ccxt\\DDoSProtection',
                '1016' => '\\ccxt\\AuthenticationError',
                '1017' => '\\ccxt\\ExchangeNotAvailable',
                '1018' => '\\ccxt\\ExchangeNotAvailable',
                '1019' => '\\ccxt\\ExchangeError',
                '1020' => '\\ccxt\\ExchangeError',
                '1021' => '\\ccxt\\ExchangeError',
                '3001' => '\\ccxt\\ExchangeError',
                '3002' => '\\ccxt\\InsufficientFunds',
                '4001' => '\\ccxt\\ExchangeError',
                '4002' => '\\ccxt\\ExchangeError',
                '4003' => '\\ccxt\\ExchangeError',
                '4004' => '\\ccxt\\ExchangeError',
            ),
            'fees' => array (
                'trading' => array (
                    'maker' => 0,
                    'taker' => 0.05 / 100,
                ),
                'funding' => array (
                    // HARDCODING IS DEPRECATED THE FEES BELOW ARE TO BE REMOVED SOON
                    'withdraw' => array (
                    ),
                ),
            ),
        ));
    }

    public function fetch_markets () {
        $response = $this->publicGetMarketSymbols ();
        $markets = $response['data'];
        $result = array ();
        for ($i = 0; $i < count ($markets); $i++) {
            $market = $markets[$i];
            $id = $market['symbol'];
            $symbolParams = explode ('-', $id);
            $baseId = $symbolParams[2];
            $quoteId = $symbolParams[1];
            $base = $this->common_currency_code($baseId);
            $quote = $this->common_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $precision = array (
                'amount' => $market['amountPrecision'],
                'price' => $market['pricePrecision'],
            );
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'active' => true,
                'precision' => $precision,
                'limits' => array (
                    'amount' => array (
                        'min' => pow (10, -$market['minOrderAmount']),
                        'max' => null,
                    ),
                    'price' => array (
                        'min' => pow (10, -$market['pricePrecision']),
                        'max' => null,
                    ),
                    'cost' => array (
                        'min' => null,
                        'max' => null,
                    ),
                ),
                'info' => $market,
            );
        }
        return $result;
    }

    public function parse_ticker ($ticker, $market = null) {
        //
        //     array (
        //         {
        //             "volume" => "190.4925000000000000",
        //             "open" => "0.0777371200000000",
        //             "market_uuid" => "38dd30bf-76c2-4777-ae2a-a3222433eef3",
        //             "market_id" => "ETH-BTC",
        //             "low" => "0.0742925600000000",
        //             "high" => "0.0789150000000000",
        //             "daily_change_perc" => "-0.3789180767180466680525339760",
        //             "daily_change" => "-0.0002945600000000",
        //             "$close" => "0.0774425600000000", // last price
        //             "bid" => array (
        //                 "price" => "0.0764777900000000",
        //                 "amount" => "6.4248000000000000"
        //             ),
        //             "ask" => {
        //                 "price" => "0.0774425600000000",
        //                 "amount" => "1.1741000000000000"
        //             }
        //         }
        //     )
        //
        if ($market !== null) {
            $marketId = $this->safe_string($ticker, 'market_id');
            if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
            }
        }
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $timestamp = $this->milliseconds ();
        $close = $this->safe_float($ticker, 'close');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'high'),
            'low' => $this->safe_float($ticker, 'low'),
            'bid' => $this->safe_float($ticker['bid'], 'price'),
            'bidVolume' => $this->safe_float($ticker['bid'], 'amount'),
            'ask' => $this->safe_float($ticker['ask'], 'price'),
            'askVolume' => $this->safe_float($ticker['ask'], 'amount'),
            'vwap' => null,
            'open' => $this->safe_float($ticker, 'open'),
            'close' => $close,
            'last' => $close,
            'previousDayClose' => null,
            'change' => $this->safe_float($ticker, 'daily_change'),
            'percentage' => $this->safe_float($ticker, 'daily_change_perc'),
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, 'volume'),
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetMarketTicker (array_merge (array (
            'symbol' => $market['id'],
        ), $params));
        return $this->parse_ticker($response['data'], $market);
    }

    public function fetch_order_book ($symbol, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetMarketsSymbolBook (array_merge (array (
            'symbol' => $this->market_id($symbol),
        ), $params));
        return $this->parse_order_book($response['data'], null, 'bids', 'asks', 'price', 'amount');
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'market_id' => $market['id'],
        );
        $response = $this->privateGetViewerOrders (array_merge ($request, $params));
        return $this->parse_orders($response['data']['edges'], $market, $since, $limit);
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = $this->parse8601 ($trade['created_at']);
        $price = floatval ($trade['price']);
        $amount = floatval ($trade['amount']);
        $symbol = $market['symbol'];
        $cost = $this->cost_to_precision($symbol, $price * $amount);
        $side = $trade['trade_side'] === 'ASK' ? 'sell' : 'buy';
        return array (
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'id' => $this->safe_string($trade, 'trade_id'),
            'order' => null,
            'type' => 'limit',
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => floatval ($cost),
            'fee' => null,
            'info' => $trade,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetMarketsSymbolTrades (array_merge (array (
            'symbol' => $market['id'],
        ), $params));
        return $this->parse_trades($response['data'], $market, $since, $limit);
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostFundAllAccount ($params);
        $result = array ( 'info' => $response );
        $balances = $response['data'];
        for ($i = 0; $i < count ($balances); $i++) {
            $balance = $balances[$i];
            $id = $balance['currency'];
            $currency = $this->common_currency_code($id);
            $account = array (
                'free' => floatval ($balance['active']),
                'used' => floatval ($balance['frozen']),
                'total' => floatval ($balance['fix']),
            );
            $result[$currency] = $account;
        }
        return $this->parse_balance($result);
    }

    public function parse_order ($order, $market = null) {
        $order_data = $this->safe_value($order, 'node');
        if ($order_data) {
            $order = $order['node'];
        }
        $marketId = $this->safe_string($order, 'market_id');
        $symbol = null;
        if ($marketId && !$market && (is_array ($this->marketsById) && array_key_exists ($marketId, $this->marketsById))) {
            $market = $this->marketsById[$marketId];
        }
        if ($market)
            $symbol = $market['symbol'];
        $timestamp = $this->parse8601 ($order['inserted_at']);
        $price = floatval ($order['price']);
        $amount = $this->safe_float($order, 'amount');
        $filled = $this->safe_float($order, 'filled_amount');
        $remaining = $amount - $filled;
        $status = $this->parse_order_status($order['state']);
        if ($status === 'filled') {
            $status = 'closed';
        }
        $side = $this->safe_string($order, 'side');
        if ($side === 'BID') {
            $side = 'buy';
        } else {
            $side = 'sell';
        }
        return array (
            'id' => $this->safe_string($order, 'id'),
            'datetime' => $this->iso8601 ($timestamp),
            'timestamp' => $timestamp,
            'status' => $status,
            'symbol' => $symbol,
            'type' => 'limit',
            'side' => $side,
            'price' => $price,
            'cost' => null,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'trades' => null,
            'fee' => null,
            'info' => $order,
        );
    }

    public function parse_order_status ($status) {
        $statuses = array (
            'CANCELED' => 'canceled',
            'PENDING' => 'open',
            'FILLED' => 'closed',
        );
        if (is_array ($statuses) && array_key_exists ($status, $statuses)) {
            return $statuses[$status];
        }
        return $status;
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->privatePostViewerOrders (array_merge (array (
            'market_id' => $market['info']['uuid'],
            'side' => ($side === 'buy' ? 'BID' : 'ASK'),
            'amount' => $amount,
            'price' => $price,
        ), $params));
        // TODO => what's the actual $response here
        return $response['data'];
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $response = $this->privatePostViewerOrdersOrderIdCancel (array_merge (array (
            'order_id' => $id,
        ), $params));
        return $response;
    }

    public function cancel_orders ($symbol = null, $params = array ()) {
        $request = array ();
        if ($symbol !== null) {
            $this->load_markets();
            $market = $this->market ($symbol);
            $request['market_id'] = $market['info']['uuid'];
        }
        //
        // the caching part to be removed
        //
        //     $response = $this->privatePostOrderCancelAll (array_merge ($request, $params));
        //     $openOrders = $this->filter_by($this->orders, 'status', 'open');
        //     for ($i = 0; $i < count ($openOrders); $i++) {
        //         $order = $openOrders[$i];
        //         $orderId = $order['id'];
        //         $this->orders[$orderId]['status'] = 'canceled';
        //     }
        //     return $response;
        //
        return $this->privatePostViewerOrdersCancelAll (array_merge ($request, $params));
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $response = $this->privateGetOrdersId (array_merge (array (
            'id' => $id,
        ), $params));
        return $this->parse_order($response['data']);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        // TODO => check if it's for open orders only
        $request = array (
            'market' => $market['id'],
        );
        if ($limit)
            $request['limit'] = $limit;
        $response = $this->privateGetOrders (array_merge ($request, $params));
        return $this->parse_orders($response['data'], $market, $since, $limit);
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'market' => $market['id'],
        );
        if ($limit) {
            $request['limit'] = $limit;
        }
        $response = $this->privateGetTrades (array_merge ($request, $params));
        $trades = $response['data']['trade_history'];
        return $this->parse_trades($trades, $market, $since, $limit);
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $response = $this->privateGetAccountsCurrency (array_merge (array (
            'currency' => $currency['id'],
        ), $params));
        $address = $this->safe_string($response['data'], 'public_key');
        $status = $address ? 'ok' : 'none';
        return array (
            'currency' => $code,
            'address' => $address,
            'status' => $status,
            'info' => $response,
        );
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'withdrawal_type' => $currency['id'],
            'address' => $address,
            'amount' => $amount,
            // 'fee' => 0.0,
            // 'asset_pin' => 'YOUR_ASSET_PIN',
        );
        if ($tag) {
            // probably it's not the same
            $request['label'] = $tag;
        }
        $response = $this->privatePostWithdrawals (array_merge ($request, $params));
        return array (
            'id' => null,
            'info' => $response,
        );
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $query = $this->omit ($params, $this->extract_params($path));
        $url = $this->urls['api'] . '/' . $this->version . '/' . $this->implode_params($path, $params);
        if ($api === 'public') {
            if ($query)
                $url .= '?' . $this->urlencode ($query);
        } else {
            $this->check_required_credentials();
            $nonce = $this->nonce ();
            $request = array (
                'type' => 'OpenAPI',
                'sub' => $this->apiKey,
                'nonce' => $nonce * 1000000000,
            );
            $headers['Authorization'] = 'Bearer ' . $this->jwt ($request, $this->secret);
            if ($method === 'GET') {
                if ($query)
                    $url .= '?' . $this->urlencode ($query);
            } else if ($method === 'POST') {
                $headers['Content-Type'] = 'application/json';
                $body = $this->json ($query);
            }
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function request ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2 ($path, $api, $method, $params, $headers, $body);
        $error = $this->safe_value($response, 'error');
        $data = $this->safe_value($response, 'data');
        if ($error || $data === null) {
            $code = $this->safe_integer($error, 'code');
            $errorClasses = array (
                '401' => '\\ccxt\\AuthenticationError',
            );
            $message = $this->safe_string($error, 'description', 'Error');
            $ErrorClass = $this->safe_string($errorClasses, $code, '\\ccxt\\ExchangeError');
            throw new $ErrorClass ($message);
        }
        return $response;
    }
}
