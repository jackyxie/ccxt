'use strict';

//  ---------------------------------------------------------------------------

const Exchange = require ('./base/Exchange');
const { ExchangeError, InsufficientFunds, AuthenticationError, ExchangeNotAvailable, InvalidNonce, DDoSProtection } = require ('./base/errors');

//  ---------------------------------------------------------------------------

module.exports = class bitforex extends Exchange {
    describe () {
        return this.deepExtend (super.describe (), {
            'id': 'bitforex',
            'name': 'BitForex',
            'countries': 'SG',
            'version': 'v1',
            'has': {
                'fetchTickers': true,
                'fetchOpenOrders': true,
                'fetchOrders': true,
                'fetchMyTrades': true,
            },
            'urls': {
                'logo': '',
                'api': 'https://api.bitforex.com/api',
                'www': 'https://www.bitforex.com',
                'doc': 'https://github.com/bitforexapi/API_Docs/wiki',
                'fees': 'https://support.bitforex.com/hc/en-us/articles/360006824872-Trading-Fees',
                'referral': 'https://www.bitforex.com',
            },
            'api': {
                'public': {
                    'get': [
                        'market/symbols',
                        'market/ticker',
                        'market/depth',
                        'market/trades',
                        'market/kline',
                    ],
                },
                'private': {
                    'post': [
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
                    ],
                },
            },
            'exceptions': {
                // see https://github.com/bitforexapi/API_Docs/wiki/%E5%BC%82%E5%B8%B8%E4%BB%A3%E7%A0%81%E8%AF%B4%E6%98%8E
                '1000': ExchangeNotAvailable, // "syntax error"
                '1001': ExchangeNotAvailable,
                '1002': ExchangeError,
                '1003': ExchangeError,
                '1010': ExchangeNotAvailable,
                '1011': AuthenticationError,
                '1012': InvalidNonce,
                '1013': AuthenticationError,
                '1014': ExchangeError,
                '1015': DDoSProtection,
                '1016': AuthenticationError,
                '1017': ExchangeNotAvailable,
                '1018': ExchangeNotAvailable,
                '1019': ExchangeError,
                '1020': ExchangeError,
                '1021': ExchangeError,
                '3001': ExchangeError,
                '3002': InsufficientFunds,
                '4001': ExchangeError,
                '4002': ExchangeError,
                '4003': ExchangeError,
                '4004': ExchangeError,
            },
            'fees': {
                'trading': {
                    'maker': 0,
                    'taker': 0.05 / 100,
                },
                'funding': {
                    // HARDCODING IS DEPRECATED THE FEES BELOW ARE TO BE REMOVED SOON
                    'withdraw': {
                    },
                },
            },
        });
    }

    async fetchMarkets () {
        let response = await this.publicGetMarketSymbols ();
        let markets = response['data'];
        let result = [];
        for (let i = 0; i < markets.length; i++) {
            let market = markets[i];
            let id = market['symbol'];
            let symbolParams = id.split ('-');
            let baseId = symbolParams[2];
            let quoteId = symbolParams[1];
            let base = this.commonCurrencyCode (baseId);
            let quote = this.commonCurrencyCode (quoteId);
            let symbol = base + '/' + quote;
            let precision = {
                'amount': market['amountPrecision'],
                'price': market['pricePrecision'],
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
                        'min': Math.pow (10, -market['minOrderAmount']),
                        'max': undefined,
                    },
                    'price': {
                        'min': Math.pow (10, -market['pricePrecision']),
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
        let response = await this.publicGetMarketTicker (this.extend ({
            'symbol': market['id'],
        }, params));
        return this.parseTicker (response['data'], market);
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
        let response = await this.privatePostFundAllAccount (params);
        let result = { 'info': response };
        let balances = response['data'];
        for (let i = 0; i < balances.length; i++) {
            let balance = balances[i];
            let id = balance['currency'];
            let currency = this.commonCurrencyCode (id);
            let account = {
                'free': parseFloat (balance['active']),
                'used': parseFloat (balance['frozen']),
                'total': parseFloat (balance['fix']),
            };
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
        let side = this.safeString (order, 'side');
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
