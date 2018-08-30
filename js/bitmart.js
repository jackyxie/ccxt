'use strict';

//  ---------------------------------------------------------------------------

const Exchange = require ('./base/Exchange');
const { ExchangeError, ExchangeNotAvailable, AuthenticationError, NotSupported } = require ('./base/errors');

//  ---------------------------------------------------------------------------

module.exports = class bitmart extends Exchange {
    describe () {
        return this.deepExtend (super.describe (), {
            'id': 'bitmart',
            'name': 'BitMart',
            'countries': 'HK',
            'rateLimit': 2000,
            'userAgent': this.userAgents['chrome39'],
            'version': 'v2',
            'accounts': undefined,
            'accountsById': undefined,
            'hostname': 'openapi.bitmart.com',
            'has': {
                'CORS': false,
                'fetchDepositAddress': false,
                'fetchOHCLV': false,
                'fetchOpenOrders': true,
                'fetchClosedOrders': true,
                'fetchOrder': true,
                'fetchOrders': true,
                'fetchOrderBook': true,
                'fetchOrderBooks': false,
                'fetchTradingLimits': false,
                'withdraw': false,
                'fetchCurrencies': false,
            },
            'timeframes': {
                '1m': 'M1',
                '3m': 'M3',
                '5m': 'M5',
                '15m': 'M15',
                '30m': 'M30',
                '1h': 'H1',
                '1d': 'D1',
                '1w': 'W1',
                '1M': 'MN',
            },
            'urls': {
                'logo': '',
                'api': 'https://openapi.bitmart.com',
                'www': 'https://www.bitmart.com/',
                'referral': 'https://www.bitmart.com/',
                'doc': 'https://github.com/bitmartexchange/api-docs',
                'fees': 'https://support.bitmart.com/hc/en-us/articles/360002043633-Fees',
            },
            'api': {
                'public': {
                    'get': [
                        'symbols_details',
                        'currencies',
                        'ticker',
                        'symbols/{symbol}/kline',
                        'symbols/{symbol}/orders',
                        'symbols/{symbol}/trades',
                        'time',
                    ],
                    'post': [
                        'authentication',
                    ],
                },
                'private': {
                    'get': [
                        'wallet',
                        'orders',
                        'orders/{order_id}',
                    ],
                    'post': [
                        'orders',
                        'orders/{order_id}/submit-cancel', // cancel order
                    ],
                    'delete': [
                        'orders',
                        'orders/{order_id}',
                    ],
                },
            },
            'fees': {
                'trading': {
                    'tierBased': false,
                    'percentage': true,
                    'maker': 0.0005,
                    'taker': 0.0005,
                },
            },
            'limits': {
                'amount': { 'min': 0.01, 'max': 100000 },
            },
            'options': {
                'createMarketBuyOrderRequiresPrice': true,
                'limits': {
                    'BTM/USDT': { 'amount': { 'min': 0.1, 'max': 10000000 }},
                    'ETC/USDT': { 'amount': { 'min': 0.001, 'max': 400000 }},
                    'ETH/USDT': { 'amount': { 'min': 0.001, 'max': 10000 }},
                    'LTC/USDT': { 'amount': { 'min': 0.001, 'max': 40000 }},
                    'BCH/USDT': { 'amount': { 'min': 0.001, 'max': 5000 }},
                    'BTC/USDT': { 'amount': { 'min': 0.001, 'max': 1000 }},
                    'ICX/ETH': { 'amount': { 'min': 0.01, 'max': 3000000 }},
                    'OMG/ETH': { 'amount': { 'min': 0.01, 'max': 500000 }},
                    'FT/USDT': { 'amount': { 'min': 1, 'max': 10000000 }},
                    'ZIL/ETH': { 'amount': { 'min': 1, 'max': 10000000 }},
                    'ZIP/ETH': { 'amount': { 'min': 1, 'max': 10000000 }},
                    'FT/BTC': { 'amount': { 'min': 1, 'max': 10000000 }},
                    'FT/ETH': { 'amount': { 'min': 1, 'max': 10000000 }},
                },
            },
            'exceptions': {
                '400': NotSupported, // Invalid request format
                '401': AuthenticationError, // Invalid API Key
                '403': ExchangeError, // You do not have access to the request resource
                '404': NotSupported, // Not Found
                '500': ExchangeNotAvailable,
            },
        });
    }

    async fetchMarkets () {
        let response = await this.publicGetSymbolsDetails ();
        let result = [];
        let markets = response;
        for (let i = 0; i < markets.length; i++) {
            let market = markets[i];
            let id = market['id'];
            let baseId = market['base_currency'];
            let quoteId = market['quote_currency'];
            let base = baseId.toUpperCase ();
            base = this.commonCurrencyCode (base);
            let quote = quoteId.toUpperCase ();
            quote = this.commonCurrencyCode (quote);
            let symbol = base + '/' + quote;
            let precision = {
                'price': market['price_max_precision'],
                'amount': 8,
            };
            let limits = {
                'price': {
                    'min': market['quote_increment'],
                    'max': undefined,
                },
                'amount': {
                    'min': market['base_min_size'],
                    'max': market['base_max_size'],
                },
            };
            if (symbol in this.options['limits']) {
                limits = this.extend (this.options['limits'][symbol], limits);
            }
            result.push ({
                'id': id,
                'symbol': symbol,
                'base': base,
                'quote': quote,
                'baseId': baseId,
                'quoteId': quoteId,
                'active': true,
                'precision': precision,
                'limits': limits,
                'info': market,
            });
        }
        return result;
    }

    async fetchBalance (params = {}) {
        await this.loadMarkets ();
        let response = await this.privateGetWallet (params);
        let result = { 'info': response };
        let balances = response;
        for (let i = 0; i < balances.length; i++) {
            let balance = balances[i];
            let currencyId = balance['id'];
            let code = currencyId.toUpperCase ();
            if (currencyId in this.currencies_by_id) {
                code = this.currencies_by_id[currencyId]['code'];
            } else {
                code = this.commonCurrencyCode (code);
            }
            let account = this.account ();
            account['free'] = parseFloat (balance['available']);
            account['used'] = parseFloat (balance['frozen']);
            account['total'] = this.sum (account['free'], account['used']);
            result[code] = account;
        }
        return result;
    }

    parseBidsAsks (orders, priceKey = 0, amountKey = 1) {
        let result = [];
        let length = orders.length;
        let halfLength = parseInt (length / 2);
        // += 2 in the for loop below won't transpile
        for (let i = 0; i < halfLength; i++) {
            let index = i * 2;
            let priceField = this.sum (index, priceKey);
            let amountField = this.sum (index, amountKey);
            result.push ([
                orders[priceField],
                orders[amountField],
            ]);
        }
        return result;
    }

    async fetchOrderBook (symbol = undefined, limit = undefined, params = {}) {
        await this.loadMarkets ();
        if (typeof limit !== 'undefined') {
            if ((limit === 20) || (limit === 100)) {
                limit = 'L' + limit.toString ();
            } else {
                throw new ExchangeError (this.id + ' fetchOrderBook supports limit of 20, 100 or no limit. Other values are not accepted');
            }
        } else {
            limit = 'full';
        }
        let request = this.extend ({
            'symbol': this.marketId (symbol),
            'level': limit, // L20, L100, full
        }, params);
        let response = await this.marketGetDepthLevelSymbol (request);
        let orderbook = response['data'];
        return this.parseOrderBook (orderbook, orderbook['ts'], 'bids', 'asks', 0, 1);
    }

    async fetchTicker (symbol, params = {}) {
        await this.loadMarkets ();
        let market = this.market (symbol);
        let ticker = await this.marketGetTickerSymbol (this.extend ({
            'symbol': market['id'],
        }, params));
        return this.parseTicker (ticker['data'], market);
    }

    parseTicker (ticker, market = undefined) {
        let timestamp = undefined;
        let symbol = undefined;
        if (typeof market === 'undefined') {
            let tickerType = this.safeString (ticker, 'type');
            if (typeof tickerType !== 'undefined') {
                let parts = tickerType.split ('.');
                let id = parts[1];
                if (id in this.markets_by_id) {
                    market = this.markets_by_id[id];
                }
            }
        }
        let values = ticker['ticker'];
        let last = values[0];
        if (typeof market !== 'undefined') {
            symbol = market['symbol'];
        }
        return {
            'symbol': symbol,
            'timestamp': timestamp,
            'datetime': this.iso8601 (timestamp),
            'high': values[7],
            'low': values[8],
            'bid': values[2],
            'bidVolume': values[3],
            'ask': values[4],
            'askVolume': values[5],
            'vwap': undefined,
            'open': undefined,
            'close': last,
            'last': last,
            'previousClose': undefined,
            'change': undefined,
            'percentage': undefined,
            'average': undefined,
            'baseVolume': values[9],
            'quoteVolume': values[10],
            'info': ticker,
        };
    }

    parseTrade (trade, market = undefined) {
        let symbol = undefined;
        if (typeof market !== 'undefined') {
            symbol = market['symbol'];
        }
        let timestamp = parseInt (trade['ts']);
        let side = trade['side'].toLowerCase ();
        let orderId = this.safeString (trade, 'id');
        let price = this.safeFloat (trade, 'price');
        let amount = this.safeFloat (trade, 'amount');
        let cost = price * amount;
        let fee = undefined;
        return {
            'id': orderId,
            'info': trade,
            'timestamp': timestamp,
            'datetime': this.iso8601 (timestamp),
            'symbol': symbol,
            'type': undefined,
            'order': orderId,
            'side': side,
            'price': price,
            'amount': amount,
            'cost': cost,
            'fee': fee,
        };
    }

    async fetchTrades (symbol, since = undefined, limit = 50, params = {}) {
        await this.loadMarkets ();
        let market = this.market (symbol);
        let request = {
            'symbol': market['id'],
            'limit': limit,
        };
        if (typeof since !== 'undefined') {
            request['timestamp'] = parseInt (since / 1000);
        }
        let response = await this.marketGetTradesSymbol (this.extend (request, params));
        return this.parseTrades (response['data'], market, since, limit);
    }

    async createOrder (symbol, type, side, amount, price = undefined, params = {}) {
        await this.loadMarkets ();
        let request = {
            'symbol': this.marketId (symbol),
            'amount': this.amountToPrecision (symbol, amount),
            'side': side,
        };
        if (type === 'limit') {
            request['price'] = this.priceToPrecision (symbol, price);
        }
        let result = await this.privatePostOrders (this.extend (request, params));
        return {
            'info': result,
            'id': result['entrust_id'],
        };
    }

    async cancelOrder (id, symbol = undefined, params = {}) {
        let response = await this.privateDeleteOrdersOrderId (this.extend ({
            'order_id': id,
            'entrust_id': id,
        }, params));
        return response;
    }

    async cancelOrders (symbol, side, params = {}) {
        await this.loadMarkets ();
        let market = this.market (symbol);
        let request = {
            'symbol': market['id'],
            'side': side,
        };
        let response = await this.privateDeleteOrders (this.extend (request, params));
        return response;
    }

    parseOrderStatus (status) {
        const statuses = {
            '1': 'open',
            '2': 'partial_filled',
            '3': 'closed',
            '4': 'canceled',
            '5': 'partial_filled',
            '6': 'partial_canceled',
        };
        if (status in statuses) {
            return statuses[status];
        }
        return status;
    }

    parseOrder (order, market = undefined) {
        let id = this.safeString (order, 'entrust_id');
        let side = this.safeString (order, 'side');
        let status = this.parseOrderStatus (this.safeString (order, 'status'));
        let symbol = undefined;
        if (typeof market === 'undefined') {
            let marketId = this.safeString (order, 'symbol');
            if (marketId in this.markets_by_id) {
                market = this.markets_by_id[marketId];
            }
        }
        let timestamp = this.safeInteger (order, 'timestamp');
        let amount = this.safeFloat (order, 'original_amount');
        let filled = this.safeFloat (order, 'executed_amount');
        let remaining = undefined;
        let price = this.safeFloat (order, 'price');
        let cost = undefined;
        if (typeof filled !== 'undefined') {
            if (typeof amount !== 'undefined') {
                remaining = amount - filled;
            }
            if (typeof price !== 'undefined') {
                cost = price * filled;
            }
        }
        let feeCurrency = undefined;
        if (typeof market !== 'undefined') {
            symbol = market['symbol'];
            feeCurrency = (side === 'buy') ? market['base'] : market['quote'];
        }
        let feeCost = this.safeFloat (order, 'fees');
        let result = {
            'info': order,
            'id': id,
            'timestamp': timestamp,
            'datetime': this.iso8601 (timestamp),
            'lastTradeTimestamp': undefined,
            'symbol': symbol,
            'type': 'limit',
            'side': side,
            'price': price,
            'cost': cost,
            'amount': amount,
            'remaining': remaining,
            'filled': filled,
            'average': undefined,
            'status': status,
            'fee': {
                'cost': feeCost,
                'currency': feeCurrency,
            },
            'trades': undefined,
        };
        return result;
    }

    async fetchOrder (id, symbol = undefined, params = {}) {
        await this.loadMarkets ();
        let request = this.extend ({
            'order_id': id,
        }, params);
        let response = await this.privateGetOrdersOrderId (request);
        return this.parseOrder (response['data']);
    }

    async fetchOpenOrders (symbol = undefined, since = undefined, limit = undefined, params = {}) {
        let result = await this.fetchOrders (symbol, since, limit, { 'states': 'submitted,partial_filled' });
        return result;
    }

    async fetchClosedOrders (symbol = undefined, since = undefined, limit = undefined, params = {}) {
        let result = await this.fetchOrders (symbol, since, limit, { 'states': 'filled' });
        return result;
    }

    async fetchOrders (symbol = undefined, since = undefined, limit = undefined, params = {}) {
        await this.loadMarkets ();
        let market = this.market (symbol);
        let request = {
            'symbol': market['id'],
            'states': 0,
        };
        if (typeof limit !== 'undefined')
            request['limit'] = limit;
        if (typeof since !== 'undefined')
            request['offset'] = since;
        let response = await this.privateGetOrders (this.extend (request, params));
        return this.parseOrders (response['orders'], market, since, limit);
    }

    parseOHLCV (ohlcv, market = undefined, timeframe = '1m', since = undefined, limit = undefined) {
        return [
            ohlcv['id'] * 1000,
            ohlcv['open'],
            ohlcv['high'],
            ohlcv['low'],
            ohlcv['close'],
            ohlcv['base_vol'],
        ];
    }

    async fetchOHLCV (symbol, timeframe = '1m', since = undefined, limit = 100, params = {}) {
        await this.loadMarkets ();
        if (typeof limit === 'undefined') {
            throw new ExchangeError (this.id + ' fetchOHLCV requires a limit argument');
        }
        let market = this.market (symbol);
        let request = this.extend ({
            'symbol': market['id'],
            'timeframe': this.timeframes[timeframe],
            'limit': limit,
        }, params);
        let response = await this.marketGetCandlesTimeframeSymbol (request);
        return this.parseOHLCVs (response['data'], market, timeframe, since, limit);
    }

    async fetchToken () {
        let tokendata = {
            'grant_type': 'client_credentials',
            'client_id': this.apiKey,
            'client_secret': this.hmac (this.apiKey + ':' + this.secret + ':' + this.memo, this.encode (this.secret), 'sha256'),
        };
        let responses = this.publicPostAuthentication (tokendata);
        this.accesstoken = responses['access_token'];
    }

    nonce () {
        return this.milliseconds ();
    }

    sign (path, api = 'public', method = 'GET', params = {}, headers = undefined, body = undefined) {
        let request = '/' + this.version + '/';
        request += this.implodeParams (path, params);
        let query = this.omit (params, this.extractParams (path));
        let url = this.urls['api'] + request;
        if ((api === 'public')) {
            if (Object.keys (query).length) {
                if (method === 'GET' || method === 'DELETE') {
                    url += '?' + this.rawencode (query);
                } else if (method === 'POST') {
                    body = this.urlencode (query);
                }
            }
        } else if (api === 'private') {
            this.fetchToken ();
            this.checkRequiredCredentials ();
            let timestamp = this.nonce ().toString ();
            let signature = '';
            if (Object.keys (query).length) {
                query = this.keysort (query);
                if (method === 'GET' || method === 'DELETE') {
                    url += '?' + this.rawencode (query);
                }
                body = this.urlencode (query);
                signature = this.hmac (body, this.encode (this.secret), 'sha256');
            }
            headers = {
                'X-BM-AUTHORIZATION': 'Bearer ' + this.accesstoken,
                'X-BM-TIMESTAMP': timestamp,
                'X-BM-SIGNATURE': signature,
                'Content-Type': 'application/json',
            };
        }
        return { 'url': url, 'method': method, 'body': body, 'headers': headers };
    }

    handleErrors (code, reason, url, method, headers, body) {
        if (typeof body !== 'string')
            return; // fallback to default error handler
        if (body.length < 2)
            return; // fallback to default error handler
        if ((body[0] === '{') || (body[0] === '[')) {
            const response = JSON.parse (body);
            let status = this.safeString (response, 'message');
            if (typeof status !== 'undefined') {
                const feedback = this.id + ' ' + body;
                if (status in this.exceptions) {
                    const exceptions = this.exceptions;
                    throw new exceptions[status] (feedback);
                }
                throw new ExchangeError (feedback);
            }
        }
    }
};
