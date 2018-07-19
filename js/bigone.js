'use strict';

//  ---------------------------------------------------------------------------

const Exchange = require ('./base/Exchange');
const { ExchangeError, InsufficientFunds, InvalidOrder, AuthenticationError } = require ('./base/errors');

//  ---------------------------------------------------------------------------

module.exports = class bigone extends Exchange {
    describe () {
        return this.deepExtend (super.describe (), {
            'id': 'bigone',
            'name': 'BigONE',
            'countries': 'GB',
            'version': 'v2',
            'has': {
                'fetchTickers': true,
                'fetchOpenOrders': true,
                'fetchOrders': true,
                'fetchMyTrades': true,
                'fetchDepositAddress': true,
                'withdraw': true,
            },
            'urls': {
                'logo': 'https://user-images.githubusercontent.com/1294454/42704835-0e48c7aa-86da-11e8-8e91-a4d1024a91b5.jpg',
                'api': 'https://big.one/api',
                'www': 'https://big.one',
                'doc': 'https://open.big.one/docs/api.html',
                'fees': 'https://help.big.one/hc/en-us/articles/115001933374-BigONE-Fee-Policy',
                'referral': 'https://b1.run/users/new?code=D3LLBVFT',
            },
            'api': {
                'public': {
                    'get': [
                        'markets',
                        'markets/{symbol}',
                        'markets/{symbol}/book',
                        'markets/{symbol}/trades',
                        'orders',
                        'orders/{id}',
                        'viewer/trades',
                    ],
                },
                'private': {
                    'get': [
                        'viewer/accounts',
                        'viewer/orders',
                        'viewer/withdrawals',
                        'viewer/deposits',
                    ],
                    'post': [
                        'viewer/orders',
                        'viewer/orders/{order_id}/cancel',
                        'viewer/orders/cancel_all',
                    ],
                },
            },
            'exceptions': {
                // see https://open.big.one/docs/api_error_codes.html
                '10001': ExchangeError, // "syntax error"
                '10002': ExchangeError, // "cannot query fields"
                '10003': ExchangeError, // "service timeout"
                '10004': ExchangeError, // "response error"
                '10005': ExchangeError, // "internal error"
                '10006': ExchangeError, // "invalid credentials"
                '10007': ExchangeError, // "params error"
                '10008': ExchangeError, // "invalid otp"
                '10009': ExchangeError, // "invalid asset pin"
                '10010': ExchangeError, // "email or password wrong"
                '10011': ExchangeError, // "system error"
                '10012': ExchangeError, // "invalid password reset token"
                '10013': ExchangeError, // "resouce not found"
                '10014': ExchangeError, // "Current broker does not support password auth."
                '10015': ExchangeError, // "Current broker does not support cookie auth."
                '10016': ExchangeError, // "broker not support login with third-party authentication"
                '10017': ExchangeError, // "favourite broker market not existed"
                '10018': AuthenticationError, // "invalid token"
                '10019': ExchangeError, // "failed to create token"
                '10022': ExchangeError, // "invalid auth schema"
                '10023': ExchangeError, // "unauthenticated"
                '10024': ExchangeError, // "invalid otp secret"
                '10025': ExchangeError, // "missing otp code"
                '10026': ExchangeError, // "invalid asset pin reset token"
                '10027': ExchangeError, // "invalid verification state"
                '10028': ExchangeError, // "invalid otp reset token"
                '30000': ExchangeError, // "Unknown Error"
                '30001': ExchangeError, // "Unknown Asset"
                '30002': ExchangeError, // "Venezia Error"
                '30003': ExchangeError, // "Invalid Field"
                '30004': InsufficientFunds, // "Insufficient Balance"
                '30005': ExchangeError, // "Perimission Denied"
                '30006': ExchangeError, // "You are not credible enough to create withdrawal."
                '30007': ExchangeError, // "Current broker does not support admin withdrawal now."
                '30008': ExchangeError, // "Memo Not Found"
                '30009': ExchangeError, // "Withdraw Suspended"
                '40001': ExchangeError, // "Market not found"
                '40002': InvalidOrder, // "Price too low"
                '40003': InvalidOrder, // "Amount too low"
                '40004': InvalidOrder, // "Filled amount too large"
                '40000': ExchangeError, // "Unknown Error"
            },
            'fees': {
                'trading': {
                    'maker': 0.1 / 100,
                    'taker': 0.1 / 100,
                },
                'funding': {
                    // HARDCODING IS DEPRECATED THE FEES BELOW ARE TO BE REMOVED SOON
                    'withdraw': {
                        'BTC': 0.002,
                        'ETH': 0.01,
                        'EOS': 0.01,
                        'ZEC': 0.002,
                        'LTC': 0.01,
                        'QTUM': 0.01,
                        // 'INK': 0.01 QTUM,
                        // 'BOT': 0.01 QTUM,
                        'ETC': 0.01,
                        'GAS': 0.0,
                        'BTS': 1.0,
                        'GXS': 0.1,
                        'BITCNY': 1.0,
                    },
                },
            },
        });
    }

    async fetchMarkets () {
        let response = await this.publicGetMarkets ();
        let markets = response['data'];
        let result = [];
        for (let i = 0; i < markets.length; i++) {
            let market = markets[i];
            let id = market['name'];
            let baseId = market['baseAsset']['symbol'];
            let quoteId = market['quoteAsset']['symbol'];
            let base = this.commonCurrencyCode (baseId);
            let quote = this.commonCurrencyCode (quoteId);
            let symbol = base + '/' + quote;
            let precision = {
                'amount': 8,
                'price': 8,
            };
            result.push ({
                'id': id,
                'symbol': symbol,
                'base': base,
                'quote': quote,
                'baseId': baseId,
                'quoteId': quoteId,
                'active': true,
                'precision': precision,
                'limits': {
                    'amount': {
                        'min': Math.pow (10, -market['baseScale']),
                        'max': undefined,
                    },
                    'price': {
                        'min': Math.pow (10, -market['quoteScale']),
                        'max': undefined,
                    },
                    'cost': {
                        'min': undefined,
                        'max': undefined,
                    },
                },
                'info': market,
            });
        }
        return result;
    }

    parseTicker (ticker, market = undefined) {
        //
        //     [
        //         {
        //             "volume": "190.4925000000000000",
        //             "open": "0.0777371200000000",
        //             "market_uuid": "38dd30bf-76c2-4777-ae2a-a3222433eef3",
        //             "market_id": "ETH-BTC",
        //             "low": "0.0742925600000000",
        //             "high": "0.0789150000000000",
        //             "daily_change_perc": "-0.3789180767180466680525339760",
        //             "daily_change": "-0.0002945600000000",
        //             "close": "0.0774425600000000", // last price
        //             "bid": {
        //                 "price": "0.0764777900000000",
        //                 "amount": "6.4248000000000000"
        //             },
        //             "ask": {
        //                 "price": "0.0774425600000000",
        //                 "amount": "1.1741000000000000"
        //             }
        //         }
        //     ]
        //
        if (typeof market !== 'undefined') {
            let marketId = this.safeString (ticker, 'market_id');
            if (marketId in this.markets_by_id) {
                market = this.markets_by_id[marketId];
            }
        }
        let symbol = undefined;
        if (typeof market !== 'undefined') {
            symbol = market['symbol'];
        }
        let timestamp = this.milliseconds ();
        let close = this.safeFloat (ticker, 'close');
        return {
            'symbol': symbol,
            'timestamp': timestamp,
            'datetime': this.iso8601 (timestamp),
            'high': this.safeFloat (ticker, 'high'),
            'low': this.safeFloat (ticker, 'low'),
            'bid': this.safeFloat (ticker['bid'], 'price'),
            'bidVolume': this.safeFloat (ticker['bid'], 'amount'),
            'ask': this.safeFloat (ticker['ask'], 'price'),
            'askVolume': this.safeFloat (ticker['ask'], 'amount'),
            'vwap': undefined,
            'open': this.safeFloat (ticker, 'open'),
            'close': close,
            'last': close,
            'previousDayClose': undefined,
            'change': this.safeFloat (ticker, 'daily_change'),
            'percentage': this.safeFloat (ticker, 'daily_change_perc'),
            'average': undefined,
            'baseVolume': this.safeFloat (ticker, 'volume'),
            'quoteVolume': undefined,
            'info': ticker,
        };
    }

    async fetchTicker (symbol, params = {}) {
        await this.loadMarkets ();
        let market = this.market (symbol);
        let response = await this.publicGetMarketsSymbol (this.extend ({
            'symbol': market['id'],
        }, params));
        return this.parseTicker (response['data']['ticker'], market);
    }

    async fetchTickers (symbols = undefined, params = {}) {
        await this.loadMarkets ();
        let response = await this.publicGetMarkets (params);
        let tickers = response['data'];
        let result = {};
        for (let i = 0; i < tickers.length; i++) {
            let ticker = this.parseTicker (tickers[i]);
            let symbol = ticker['symbol'];
            result[symbol] = ticker;
        }
        return result;
    }

    async fetchOrderBook (symbol, params = {}) {
        await this.loadMarkets ();
        let response = await this.publicGetMarketsSymbolBook (this.extend ({
            'symbol': this.marketId (symbol),
        }, params));
        return this.parseOrderBook (response['data'], undefined, 'bids', 'asks', 'price', 'amount');
    }

    async fetchOrders (symbol = undefined, since = undefined, limit = undefined, params = {}) {
        await this.loadMarkets ();
        let market = this.market (symbol);
        let request = {
            'market_id': market['id'],
        };
        let response = await this.privateGetViewerOrders (this.extend (request, params));
        return this.parseOrders (response['data']['edges'], market, since, limit);
    }

    parseTrade (trade, market = undefined) {
        let timestamp = this.parse8601 (trade['created_at']);
        let price = parseFloat (trade['price']);
        let amount = parseFloat (trade['amount']);
        let symbol = market['symbol'];
        let cost = this.costToPrecision (symbol, price * amount);
        let side = trade['trade_side'] === 'ASK' ? 'sell' : 'buy';
        return {
            'timestamp': timestamp,
            'datetime': this.iso8601 (timestamp),
            'symbol': symbol,
            'id': this.safeString (trade, 'trade_id'),
            'order': undefined,
            'type': 'limit',
            'side': side,
            'price': price,
            'amount': amount,
            'cost': parseFloat (cost),
            'fee': undefined,
            'info': trade,
        };
    }

    async fetchTrades (symbol, since = undefined, limit = undefined, params = {}) {
        await this.loadMarkets ();
        let market = this.market (symbol);
        let response = await this.publicGetMarketsSymbolTrades (this.extend ({
            'symbol': market['id'],
        }, params));
        return this.parseTrades (response['data'], market, since, limit);
    }

    async fetchBalance (params = {}) {
        await this.loadMarkets ();
        let response = await this.privateGetViewerAccounts (params);
        let result = { 'info': response };
        let balances = response['data'];
        for (let i = 0; i < balances.length; i++) {
            let balance = balances[i];
            let id = balance['asset_id'];
            let currency = this.commonCurrencyCode (id);
            let account = {
                'free': 0.0,
                'used': parseFloat (balance['locked_balance']),
                'total': parseFloat (balance['balance']),
            };
            account['total'] = this.sum (account['total'], -account['used']);
            result[currency] = account;
        }
        return this.parseBalance (result);
    }

    parseOrder (order, market = undefined) {
        let order_data = this.safeValue (order, 'node');
        if (order_data) {
            order = order['node'];
        }
        let marketId = this.safeString (order, 'market_id');
        let symbol = undefined;
        if (marketId && !market && (marketId in this.marketsById)) {
            market = this.marketsById[marketId];
        }
        if (market)
            symbol = market['symbol'];
        let timestamp = this.parse8601 (order['inserted_at']);
        let price = parseFloat (order['price']);
        let amount = this.safeFloat (order, 'amount');
        let filled = this.safeFloat (order, 'filled_amount');
        let remaining = amount - filled;
        let status = this.parseOrderStatus (order['state']);
        if (status === 'filled') {
            status = 'closed';
        }
        let side = this.safeInteger (order, 'side');
        if (side === 'BID') {
            side = 'buy';
        } else {
            side = 'sell';
        }
        return {
            'id': this.safeString (order, 'id'),
            'datetime': this.iso8601 (timestamp),
            'timestamp': timestamp,
            'status': status,
            'symbol': symbol,
            'type': 'limit',
            'side': side,
            'price': price,
            'cost': undefined,
            'amount': amount,
            'filled': filled,
            'remaining': remaining,
            'trades': undefined,
            'fee': undefined,
            'info': order,
        };
    }

    parseOrderStatus (status) {
        const statuses = {
            'CANCELED': 'canceled',
            'PENDING': 'open',
            'FILLED': 'closed',
        };
        if (status in statuses) {
            return statuses[status];
        }
        return status;
    }

    async createOrder (symbol, type, side, amount, price = undefined, params = {}) {
        await this.loadMarkets ();
        let market = this.market (symbol);
        let response = await this.privatePostViewerOrders (this.extend ({
            'market_id': market['info']['uuid'],
            'side': (side === 'buy' ? 'BID' : 'ASK'),
            'amount': amount,
            'price': price,
        }, params));
        // TODO: what's the actual response here
        return response['data'];
    }

    async cancelOrder (id, symbol = undefined, params = {}) {
        let response = await this.privatePostViewerOrdersOrderIdCancel (this.extend ({
            'order_id': id,
        }, params));
        return response;
    }

    async cancelOrders (symbol = undefined, params = {}) {
        let request = {};
        if (typeof symbol !== 'undefined') {
            await this.loadMarkets ();
            let market = this.market (symbol);
            request['market_id'] = market['info']['uuid'];
        }
        //
        // the caching part to be removed
        //
        //     let response = await this.privatePostOrderCancelAll (this.extend (request, params));
        //     let openOrders = this.filterBy (this.orders, 'status', 'open');
        //     for (let i = 0; i < openOrders.length; i++) {
        //         let order = openOrders[i];
        //         let orderId = order['id'];
        //         this.orders[orderId]['status'] = 'canceled';
        //     }
        //     return response;
        //
        return await this.privatePostViewerOrdersCancelAll (this.extend (request, params));
    }

    async fetchOrder (id, symbol = undefined, params = {}) {
        await this.loadMarkets ();
        let response = await this.privateGetOrdersId (this.extend ({
            'id': id,
        }, params));
        return this.parseOrder (response['data']);
    }

    async fetchOpenOrders (symbol = undefined, since = undefined, limit = undefined, params = {}) {
        await this.loadMarkets ();
        let market = this.market (symbol);
        // TODO: check if it's for open orders only
        let request = {
            'market': market['id'],
        };
        if (limit)
            request['limit'] = limit;
        let response = await this.privateGetOrders (this.extend (request, params));
        return this.parseOrders (response['data'], market, since, limit);
    }

    async fetchMyTrades (symbol = undefined, since = undefined, limit = undefined, params = {}) {
        await this.loadMarkets ();
        let market = this.market (symbol);
        let request = {
            'market': market['id'],
        };
        if (limit) {
            request['limit'] = limit;
        }
        let response = await this.privateGetTrades (this.extend (request, params));
        let trades = response['data']['trade_history'];
        return this.parseTrades (trades, market, since, limit);
    }

    async fetchDepositAddress (code, params = {}) {
        await this.loadMarkets ();
        let currency = this.currency (code);
        let response = await this.privateGetAccountsCurrency (this.extend ({
            'currency': currency['id'],
        }, params));
        let address = this.safeString (response['data'], 'public_key');
        let status = address ? 'ok' : 'none';
        return {
            'currency': code,
            'address': address,
            'status': status,
            'info': response,
        };
    }

    async withdraw (code, amount, address, tag = undefined, params = {}) {
        await this.loadMarkets ();
        let currency = this.currency (code);
        let request = {
            'withdrawal_type': currency['id'],
            'address': address,
            'amount': amount,
            // 'fee': 0.0,
            // 'asset_pin': 'YOUR_ASSET_PIN',
        };
        if (tag) {
            // probably it's not the same
            request['label'] = tag;
        }
        let response = await this.privatePostWithdrawals (this.extend (request, params));
        return {
            'id': undefined,
            'info': response,
        };
    }

    sign (path, api = 'public', method = 'GET', params = {}, headers = undefined, body = undefined) {
        let query = this.omit (params, this.extractParams (path));
        let url = this.urls['api'] + '/' + this.version + '/' + this.implodeParams (path, params);
        if (api === 'public') {
            if (Object.keys (query).length)
                url += '?' + this.urlencode (query);
        } else {
            this.checkRequiredCredentials ();
            let nonce = this.nonce ();
            let request = {
                'type': 'OpenAPI',
                'sub': this.apiKey,
                'nonce': nonce * 1000000000,
            };
            headers['Authorization'] = 'Bearer ' + this.jwt (request, this.secret);
            if (method === 'GET') {
                if (Object.keys (query).length)
                    url += '?' + this.urlencode (query);
            } else if (method === 'POST') {
                headers['Content-Type'] = 'application/json';
                body = this.json (query);
            }
        }
        return { 'url': url, 'method': method, 'body': body, 'headers': headers };
    }

    async request (path, api = 'public', method = 'GET', params = {}, headers = undefined, body = undefined) {
        let response = await this.fetch2 (path, api, method, params, headers, body);
        let error = this.safeValue (response, 'error');
        let data = this.safeValue (response, 'data');
        if (error || typeof data === 'undefined') {
            let code = this.safeInteger (error, 'code');
            let errorClasses = {
                '401': AuthenticationError,
            };
            let message = this.safeString (error, 'description', 'Error');
            let ErrorClass = this.safeString (errorClasses, code, ExchangeError);
            throw new ErrorClass (message);
        }
        return response;
    }
};
