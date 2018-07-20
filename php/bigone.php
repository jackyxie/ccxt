<?php

namespace ccxt;

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

use Exception as Exception; // a common import

class bigone extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'bigone',
            'name' => 'BigONE',
            'countries' => 'GB',
            'version' => 'v2',
            'has' => array (
                'fetchTickers' => true,
                'fetchOpenOrders' => true,
                'fetchOrders' => true,
                'fetchMyTrades' => true,
                'fetchDepositAddress' => true,
                'withdraw' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/42704835-0e48c7aa-86da-11e8-8e91-a4d1024a91b5.jpg',
                'api' => 'https://big.one/api',
                'www' => 'https://big.one',
                'doc' => 'https://open.big.one/docs/api.html',
                'fees' => 'https://help.big.one/hc/en-us/articles/115001933374-BigONE-Fee-Policy',
                'referral' => 'https://b1.run/users/new?code=D3LLBVFT',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'markets',
                        'markets/{symbol}',
                        'markets/{symbol}/book',
                        'markets/{symbol}/trades',
                        'orders',
                        'orders/{id}',
                        'viewer/trades',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'viewer/accounts',
                        'viewer/orders',
                        'viewer/withdrawals',
                        'viewer/deposits',
                    ),
                    'post' => array (
                        'viewer/orders',
                        'viewer/orders/{order_id}/cancel',
                        'viewer/orders/cancel_all',
                    ),
                ),
            ),
            'exceptions' => array (
                // see https://open.big.one/docs/api_error_codes.html
                '10001' => '\\ccxt\\ExchangeError', // "syntax error"
                '10002' => '\\ccxt\\ExchangeError', // "cannot query fields"
                '10003' => '\\ccxt\\ExchangeError', // "service timeout"
                '10004' => '\\ccxt\\ExchangeError', // "response error"
                '10005' => '\\ccxt\\ExchangeError', // "internal error"
                '10006' => '\\ccxt\\ExchangeError', // "invalid credentials"
                '10007' => '\\ccxt\\ExchangeError', // "params error"
                '10008' => '\\ccxt\\ExchangeError', // "invalid otp"
                '10009' => '\\ccxt\\ExchangeError', // "invalid asset pin"
                '10010' => '\\ccxt\\ExchangeError', // "email or password wrong"
                '10011' => '\\ccxt\\ExchangeError', // "system error"
                '10012' => '\\ccxt\\ExchangeError', // "invalid password reset token"
                '10013' => '\\ccxt\\ExchangeError', // "resouce not found"
                '10014' => '\\ccxt\\ExchangeError', // "Current broker does not support password auth."
                '10015' => '\\ccxt\\ExchangeError', // "Current broker does not support cookie auth."
                '10016' => '\\ccxt\\ExchangeError', // "broker not support login with third-party authentication"
                '10017' => '\\ccxt\\ExchangeError', // "favourite broker market not existed"
                '10018' => '\\ccxt\\AuthenticationError', // "invalid token"
                '10019' => '\\ccxt\\ExchangeError', // "failed to create token"
                '10022' => '\\ccxt\\ExchangeError', // "invalid auth schema"
                '10023' => '\\ccxt\\ExchangeError', // "unauthenticated"
                '10024' => '\\ccxt\\ExchangeError', // "invalid otp secret"
                '10025' => '\\ccxt\\ExchangeError', // "missing otp code"
                '10026' => '\\ccxt\\ExchangeError', // "invalid asset pin reset token"
                '10027' => '\\ccxt\\ExchangeError', // "invalid verification state"
                '10028' => '\\ccxt\\ExchangeError', // "invalid otp reset token"
                '30000' => '\\ccxt\\ExchangeError', // "Unknown Error"
                '30001' => '\\ccxt\\ExchangeError', // "Unknown Asset"
                '30002' => '\\ccxt\\ExchangeError', // "Venezia Error"
                '30003' => '\\ccxt\\ExchangeError', // "Invalid Field"
                '30004' => '\\ccxt\\InsufficientFunds', // "Insufficient Balance"
                '30005' => '\\ccxt\\ExchangeError', // "Perimission Denied"
                '30006' => '\\ccxt\\ExchangeError', // "You are not credible enough to create withdrawal."
                '30007' => '\\ccxt\\ExchangeError', // "Current broker does not support admin withdrawal now."
                '30008' => '\\ccxt\\ExchangeError', // "Memo Not Found"
                '30009' => '\\ccxt\\ExchangeError', // "Withdraw Suspended"
                '40001' => '\\ccxt\\ExchangeError', // "Market not found"
                '40002' => '\\ccxt\\InvalidOrder', // "Price too low"
                '40003' => '\\ccxt\\InvalidOrder', // "Amount too low"
                '40004' => '\\ccxt\\InvalidOrder', // "Filled amount too large"
                '40000' => '\\ccxt\\ExchangeError', // "Unknown Error"
            ),
            'fees' => array (
                'trading' => array (
                    'maker' => 0.1 / 100,
                    'taker' => 0.1 / 100,
                ),
                'funding' => array (
                    // HARDCODING IS DEPRECATED THE FEES BELOW ARE TO BE REMOVED SOON
                    'withdraw' => array (
                        'BTC' => 0.002,
                        'ETH' => 0.01,
                        'EOS' => 0.01,
                        'ZEC' => 0.002,
                        'LTC' => 0.01,
                        'QTUM' => 0.01,
                        // 'INK' => 0.01 QTUM,
                        // 'BOT' => 0.01 QTUM,
                        'ETC' => 0.01,
                        'GAS' => 0.0,
                        'BTS' => 1.0,
                        'GXS' => 0.1,
                        'BITCNY' => 1.0,
                    ),
                ),
            ),
        ));
    }

    public function fetch_markets () {
        $response = $this->publicGetMarkets ();
        $markets = $response['data'];
        $result = array ();
        for ($i = 0; $i < count ($markets); $i++) {
            $market = $markets[$i];
            $id = $market['name'];
            $baseId = $market['baseAsset']['symbol'];
            $quoteId = $market['quoteAsset']['symbol'];
            $base = $this->common_currency_code($baseId);
            $quote = $this->common_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $precision = array (
                'amount' => 8,
                'price' => 8,
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
                        'min' => pow (10, -$market['baseScale']),
                        'max' => null,
                    ),
                    'price' => array (
                        'min' => pow (10, -$market['quoteScale']),
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
        $response = $this->publicGetMarketsSymbol (array_merge (array (
            'symbol' => $market['id'],
        ), $params));
        return $this->parse_ticker($response['data']['ticker'], $market);
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetMarkets ($params);
        $tickers = $response['data'];
        $result = array ();
        for ($i = 0; $i < count ($tickers); $i++) {
            $ticker = $this->parse_ticker($tickers[$i]);
            $symbol = $ticker['symbol'];
            $result[$symbol] = $ticker;
        }
        return $result;
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
        $response = $this->privateGetViewerAccounts ($params);
        $result = array ( 'info' => $response );
        $balances = $response['data'];
        for ($i = 0; $i < count ($balances); $i++) {
            $balance = $balances[$i];
            $id = $balance['asset_id'];
            $currency = $this->common_currency_code($id);
            $account = array (
                'free' => 0.0,
                'used' => floatval ($balance['locked_balance']),
                'total' => floatval ($balance['balance']),
            );
            $account['free'] = $this->sum ($account['total'], -$account['used']);
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
