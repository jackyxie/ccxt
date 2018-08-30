<?php

namespace ccxt;

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

use Exception as Exception; // a common import

class bitmart extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'bitmart',
            'name' => 'BitMart',
            'countries' => 'CN',
            'rateLimit' => 2000,
            'userAgent' => $this->userAgents['chrome39'],
            'version' => 'v2',
            'accounts' => null,
            'accountsById' => null,
            'hostname' => 'openapi.bitmart.com',
            'has' => array (
                'CORS' => false,
                'fetchDepositAddress' => false,
                'fetchOHCLV' => false,
                'fetchOpenOrders' => true,
                'fetchClosedOrders' => true,
                'fetchOrder' => true,
                'fetchOrders' => true,
                'fetchOrderBook' => true,
                'fetchOrderBooks' => false,
                'fetchTradingLimits' => false,
                'withdraw' => false,
                'fetchCurrencies' => false,
            ),
            'timeframes' => array (
                '1m' => 'M1',
                '3m' => 'M3',
                '5m' => 'M5',
                '15m' => 'M15',
                '30m' => 'M30',
                '1h' => 'H1',
                '1d' => 'D1',
                '1w' => 'W1',
                '1M' => 'MN',
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/42244210-c8c42e1e-7f1c-11e8-8710-a5fb63b165c4.jpg',
                'api' => 'https://openapi.bitmart.com',
                'www' => 'https://www.bitmart.com/',
                'referral' => 'https://www.bitmart.com/',
                'doc' => 'https://github.com/bitmartexchange/api-docs',
                'fees' => 'https://support.bitmart.com/hc/en-us/articles/360002043633-Fees',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'symbols_details',
                        'currencies',
                        'ticker',
                        'symbols/{symbol}/kline',
                        'symbols/{symbol}/orders',
                        'symbols/{symbol}/trades',
                        'time',
                    ),
                    'post' => array (
                        'authentication',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'wallet',
                        'orders',
                        'orders/{order_id}',
                    ),
                    'post' => array (
                        'orders',
                        'orders/{order_id}/submit-cancel', // cancel order
                    ),
                    'delete' => array (
                        'orders',
                        'orders/{order_id}',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'tierBased' => false,
                    'percentage' => true,
                    'maker' => 0.0005,
                    'taker' => 0.0005,
                ),
            ),
            'limits' => array (
                'amount' => array ( 'min' => 0.01, 'max' => 100000 ),
            ),
            'options' => array (
                'createMarketBuyOrderRequiresPrice' => true,
                'limits' => array (
                    'BTM/USDT' => array ( 'amount' => array ( 'min' => 0.1, 'max' => 10000000 )),
                    'ETC/USDT' => array ( 'amount' => array ( 'min' => 0.001, 'max' => 400000 )),
                    'ETH/USDT' => array ( 'amount' => array ( 'min' => 0.001, 'max' => 10000 )),
                    'LTC/USDT' => array ( 'amount' => array ( 'min' => 0.001, 'max' => 40000 )),
                    'BCH/USDT' => array ( 'amount' => array ( 'min' => 0.001, 'max' => 5000 )),
                    'BTC/USDT' => array ( 'amount' => array ( 'min' => 0.001, 'max' => 1000 )),
                    'ICX/ETH' => array ( 'amount' => array ( 'min' => 0.01, 'max' => 3000000 )),
                    'OMG/ETH' => array ( 'amount' => array ( 'min' => 0.01, 'max' => 500000 )),
                    'FT/USDT' => array ( 'amount' => array ( 'min' => 1, 'max' => 10000000 )),
                    'ZIL/ETH' => array ( 'amount' => array ( 'min' => 1, 'max' => 10000000 )),
                    'ZIP/ETH' => array ( 'amount' => array ( 'min' => 1, 'max' => 10000000 )),
                    'FT/BTC' => array ( 'amount' => array ( 'min' => 1, 'max' => 10000000 )),
                    'FT/ETH' => array ( 'amount' => array ( 'min' => 1, 'max' => 10000000 )),
                ),
            ),
            'exceptions' => array (
                '400' => '\\ccxt\\NotSupported', // Invalid request format
                '401' => '\\ccxt\\AuthenticationError', // Invalid API Key
                '403' => '\\ccxt\\ExchangeError', // You do not have access to the request resource
                '404' => '\\ccxt\\NotSupported', // Not Found
                '500' => '\\ccxt\\ExchangeNotAvailable',
            ),
        ));
    }

    public function fetch_markets () {
        $response = $this->publicGetSymbolsDetails ();
        $result = array ();
        $markets = $response;
        for ($i = 0; $i < count ($markets); $i++) {
            $market = $markets[$i];
            $id = $market['id'];
            $baseId = $market['base_currency'];
            $quoteId = $market['quote_currency'];
            $base = strtoupper ($baseId);
            $base = $this->common_currency_code($base);
            $quote = strtoupper ($quoteId);
            $quote = $this->common_currency_code($quote);
            $symbol = $base . '/' . $quote;
            $precision = array (
                'price' => $market['price_max_precision'],
                'amount' => 8,
            );
            $limits = array (
                'price' => array (
                    'min' => $market['quote_increment'],
                    'max' => null,
                ),
                'amount' => array (
                    'min' => $market['base_min_size'],
                    'max' => $market['base_max_size'],
                ),
            );
            if (is_array ($this->options['limits']) && array_key_exists ($symbol, $this->options['limits'])) {
                $limits = array_merge ($this->options['limits'][$symbol], $limits);
            }
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'active' => true,
                'precision' => $precision,
                'limits' => $limits,
                'info' => $market,
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privateGetWallet ($params);
        $result = array ( 'info' => $response );
        $balances = $response;
        for ($i = 0; $i < count ($balances); $i++) {
            $balance = $balances[$i];
            $currencyId = $balance['id'];
            $code = strtoupper ($currencyId);
            if (is_array ($this->currencies_by_id) && array_key_exists ($currencyId, $this->currencies_by_id)) {
                $code = $this->currencies_by_id[$currencyId]['code'];
            } else {
                $code = $this->common_currency_code($code);
            }
            $account = $this->account ();
            $account['free'] = floatval ($balance['available']);
            $account['used'] = floatval ($balance['frozen']);
            $account['total'] = $this->sum ($account['free'], $account['used']);
            $result[$code] = $account;
        }
        return $result;
    }

    public function parse_bids_asks ($orders, $priceKey = 0, $amountKey = 1) {
        $result = array ();
        $length = is_array ($orders) ? count ($orders) : 0;
        $halfLength = intval ($length / 2);
        // .= 2 in the for loop below won't transpile
        for ($i = 0; $i < $halfLength; $i++) {
            $index = $i * 2;
            $priceField = $this->sum ($index, $priceKey);
            $amountField = $this->sum ($index, $amountKey);
            $result[] = [
                $orders[$priceField],
                $orders[$amountField],
            ];
        }
        return $result;
    }

    public function fetch_order_book ($symbol = null, $limit = null, $params = array ()) {
        $this->load_markets();
        if ($limit !== null) {
            if (($limit === 20) || ($limit === 100)) {
                $limit = 'L' . (string) $limit;
            } else {
                throw new ExchangeError ($this->id . ' fetchOrderBook supports $limit of 20, 100 or no $limit-> Other values are not accepted');
            }
        } else {
            $limit = 'full';
        }
        $request = array_merge (array (
            'symbol' => $this->market_id($symbol),
            'level' => $limit, // L20, L100, full
        ), $params);
        $response = $this->marketGetDepthLevelSymbol ($request);
        $orderbook = $response['data'];
        return $this->parse_order_book($orderbook, $orderbook['ts'], 'bids', 'asks', 0, 1);
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $ticker = $this->marketGetTickerSymbol (array_merge (array (
            'symbol' => $market['id'],
        ), $params));
        return $this->parse_ticker($ticker['data'], $market);
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = null;
        $symbol = null;
        if ($market === null) {
            $tickerType = $this->safe_string($ticker, 'type');
            if ($tickerType !== null) {
                $parts = explode ('.', $tickerType);
                $id = $parts[1];
                if (is_array ($this->markets_by_id) && array_key_exists ($id, $this->markets_by_id)) {
                    $market = $this->markets_by_id[$id];
                }
            }
        }
        $values = $ticker['ticker'];
        $last = $values[0];
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $values[7],
            'low' => $values[8],
            'bid' => $values[2],
            'bidVolume' => $values[3],
            'ask' => $values[4],
            'askVolume' => $values[5],
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => $values[9],
            'quoteVolume' => $values[10],
            'info' => $ticker,
        );
    }

    public function parse_trade ($trade, $market = null) {
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $timestamp = intval ($trade['ts']);
        $side = strtolower ($trade['side']);
        $orderId = $this->safe_string($trade, 'id');
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'amount');
        $cost = $price * $amount;
        $fee = null;
        return array (
            'id' => $orderId,
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'type' => null,
            'order' => $orderId,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => $fee,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = 50, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
            'limit' => $limit,
        );
        if ($since !== null) {
            $request['timestamp'] = intval ($since / 1000);
        }
        $response = $this->marketGetTradesSymbol (array_merge ($request, $params));
        return $this->parse_trades($response['data'], $market, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'symbol' => $this->market_id($symbol),
            'amount' => $this->amount_to_precision($symbol, $amount),
            'side' => $side,
        );
        if ($type === 'limit') {
            $request['price'] = $this->price_to_precision($symbol, $price);
        }
        $result = $this->privatePostOrders (array_merge ($request, $params));
        return array (
            'info' => $result,
            'id' => $result['entrust_id'],
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $response = $this->privateDeleteOrdersOrderId (array_merge (array (
            'order_id' => $id,
            'entrust_id' => $id,
        ), $params));
        return $response;
    }

    public function cancel_orders ($symbol, $side, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
            'side' => $side,
        );
        $response = $this->privateDeleteOrders (array_merge ($request, $params));
        return $response;
    }

    public function parse_order_status ($status) {
        $statuses = array (
            '1' => 'open',
            '2' => 'partial_filled',
            '3' => 'closed',
            '4' => 'canceled',
            '5' => 'partial_filled',
            '6' => 'partial_canceled',
        );
        if (is_array ($statuses) && array_key_exists ($status, $statuses)) {
            return $statuses[$status];
        }
        return $status;
    }

    public function parse_order ($order, $market = null) {
        $id = $this->safe_string($order, 'entrust_id');
        $side = $this->safe_string($order, 'side');
        $status = $this->parse_order_status($this->safe_string($order, 'status'));
        $symbol = null;
        if ($market === null) {
            $marketId = $this->safe_string($order, 'symbol');
            if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
            }
        }
        $timestamp = $this->safe_integer($order, 'timestamp');
        $amount = $this->safe_float($order, 'original_amount');
        $filled = $this->safe_float($order, 'executed_amount');
        $remaining = null;
        $price = $this->safe_float($order, 'price');
        $cost = null;
        if ($filled !== null) {
            if ($amount !== null) {
                $remaining = $amount - $filled;
            }
            if ($price !== null) {
                $cost = $price * $filled;
            }
        }
        $feeCurrency = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
            $feeCurrency = ($side === 'buy') ? $market['base'] : $market['quote'];
        }
        $feeCost = $this->safe_float($order, 'fees');
        $result = array (
            'info' => $order,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'symbol' => $symbol,
            'type' => 'limit',
            'side' => $side,
            'price' => $price,
            'cost' => $cost,
            'amount' => $amount,
            'remaining' => $remaining,
            'filled' => $filled,
            'average' => null,
            'status' => $status,
            'fee' => array (
                'cost' => $feeCost,
                'currency' => $feeCurrency,
            ),
            'trades' => null,
        );
        return $result;
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array_merge (array (
            'order_id' => $id,
        ), $params);
        $response = $this->privateGetOrdersOrderId ($request);
        return $this->parse_order($response['data']);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $result = $this->fetch_orders($symbol, $since, $limit, array ( 'states' => 'submitted,partial_filled' ));
        return $result;
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $result = $this->fetch_orders($symbol, $since, $limit, array ( 'states' => 'filled' ));
        return $result;
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
            'states' => 0,
        );
        if ($limit !== null)
            $request['limit'] = $limit;
        if ($since !== null)
            $request['offset'] = $since;
        $response = $this->privateGetOrders (array_merge ($request, $params));
        return $this->parse_orders($response['orders'], $market, $since, $limit);
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '1m', $since = null, $limit = null) {
        return [
            $ohlcv['id'] * 1000,
            $ohlcv['open'],
            $ohlcv['high'],
            $ohlcv['low'],
            $ohlcv['close'],
            $ohlcv['base_vol'],
        ];
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = 100, $params = array ()) {
        $this->load_markets();
        if ($limit === null) {
            throw new ExchangeError ($this->id . ' fetchOHLCV requires a $limit argument');
        }
        $market = $this->market ($symbol);
        $request = array_merge (array (
            'symbol' => $market['id'],
            'timeframe' => $this->timeframes[$timeframe],
            'limit' => $limit,
        ), $params);
        $response = $this->marketGetCandlesTimeframeSymbol ($request);
        return $this->parse_ohlcvs($response['data'], $market, $timeframe, $since, $limit);
    }

    public function fetch_token () {
        $tokendata = array (
            'grant_type' => 'client_credentials',
            'client_id' => $this->apiKey,
            'client_secret' => $this->hmac ($this->apiKey . ':' . $this->secret . ':' . $this->memo, $this->encode ($this->secret), 'sha256'),
        );
        $responses = $this->publicPostAuthentication ($tokendata);
        $this->accesstoken = $responses['access_token'];
    }

    public function nonce () {
        return $this->milliseconds ();
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $request = '/' . $this->version . '/';
        $request .= $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        $url = $this->urls['api'] . $request;
        if (($api === 'public')) {
            if ($query) {
                if ($method === 'GET' || $method === 'DELETE') {
                    $url .= '?' . $this->rawencode ($query);
                } else if ($method === 'POST') {
                    $body = $this->urlencode ($query);
                }
            }
        } else if ($api === 'private') {
            $this->fetch_token ();
            $this->check_required_credentials();
            $timestamp = (string) $this->nonce ();
            $signature = '';
            if ($query) {
                $query = $this->keysort ($query);
                if ($method === 'GET' || $method === 'DELETE') {
                    $url .= '?' . $this->rawencode ($query);
                }
                $body = $this->urlencode ($query);
                $signature = $this->hmac ($body, $this->encode ($this->secret), 'sha256');
            }
            $headers = array (
                'X-BM-AUTHORIZATION' => 'Bearer ' . $this->accesstoken,
                'X-BM-TIMESTAMP' => $timestamp,
                'X-BM-SIGNATURE' => $signature,
                'Content-Type' => 'application/json',
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body) {
        if (gettype ($body) !== 'string')
            return; // fallback to default error handler
        if (strlen ($body) < 2)
            return; // fallback to default error handler
        if (($body[0] === '{') || ($body[0] === '[')) {
            $response = json_decode ($body, $as_associative_array = true);
            $status = $this->safe_string($response, 'message');
            if ($status !== null) {
                $feedback = $this->id . ' ' . $body;
                if (is_array ($this->exceptions) && array_key_exists ($status, $this->exceptions)) {
                    $exceptions = $this->exceptions;
                    throw new $exceptions[$status] ($feedback);
                }
                throw new ExchangeError ($feedback);
            }
        }
    }
}
