# -*- coding: utf-8 -*-

# PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
# https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

from ccxt.async_support.base.exchange import Exchange

# -----------------------------------------------------------------------------

try:
    basestring  # Python 3
except NameError:
    basestring = str  # Python 2
import hashlib
import json
from ccxt.base.errors import ExchangeError
from ccxt.base.errors import AuthenticationError
from ccxt.base.errors import NotSupported
from ccxt.base.errors import ExchangeNotAvailable


class bitmart (Exchange):

    def describe(self):
        return self.deep_extend(super(bitmart, self).describe(), {
            'id': 'bitmart',
            'name': 'BitMart',
            'countries': 'CN',
            'rateLimit': 2000,
            'userAgent': self.userAgents['chrome39'],
            'version': 'v2',
            'accounts': None,
            'accountsById': None,
            'hostname': 'openapi.bitmart.com',
            'has': {
                'CORS': False,
                'fetchDepositAddress': False,
                'fetchOHCLV': False,
                'fetchOpenOrders': True,
                'fetchClosedOrders': True,
                'fetchOrder': True,
                'fetchOrders': True,
                'fetchOrderBook': True,
                'fetchOrderBooks': False,
                'fetchTradingLimits': False,
                'withdraw': False,
                'fetchCurrencies': False,
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
                'logo': 'https://user-images.githubusercontent.com/1294454/42244210-c8c42e1e-7f1c-11e8-8710-a5fb63b165c4.jpg',
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
                        'orders/{order_id}/submit-cancel',  # cancel order
                    ],
                    'delete': [
                        'orders',
                        'orders/{order_id}',
                    ],
                },
            },
            'fees': {
                'trading': {
                    'tierBased': False,
                    'percentage': True,
                    'maker': 0.0005,
                    'taker': 0.0005,
                },
            },
            'limits': {
                'amount': {'min': 0.01, 'max': 100000},
            },
            'options': {
                'createMarketBuyOrderRequiresPrice': True,
                'limits': {
                    'BTM/USDT': {'amount': {'min': 0.1, 'max': 10000000}},
                    'ETC/USDT': {'amount': {'min': 0.001, 'max': 400000}},
                    'ETH/USDT': {'amount': {'min': 0.001, 'max': 10000}},
                    'LTC/USDT': {'amount': {'min': 0.001, 'max': 40000}},
                    'BCH/USDT': {'amount': {'min': 0.001, 'max': 5000}},
                    'BTC/USDT': {'amount': {'min': 0.001, 'max': 1000}},
                    'ICX/ETH': {'amount': {'min': 0.01, 'max': 3000000}},
                    'OMG/ETH': {'amount': {'min': 0.01, 'max': 500000}},
                    'FT/USDT': {'amount': {'min': 1, 'max': 10000000}},
                    'ZIL/ETH': {'amount': {'min': 1, 'max': 10000000}},
                    'ZIP/ETH': {'amount': {'min': 1, 'max': 10000000}},
                    'FT/BTC': {'amount': {'min': 1, 'max': 10000000}},
                    'FT/ETH': {'amount': {'min': 1, 'max': 10000000}},
                },
            },
            'exceptions': {
                '400': NotSupported,  # Invalid request format
                '401': AuthenticationError,  # Invalid API Key
                '403': ExchangeError,  # You do not have access to the request resource
                '404': NotSupported,  # Not Found
                '500': ExchangeNotAvailable,
            },
        })

    async def fetch_markets(self):
        response = await self.publicGetSymbolsDetails()
        result = []
        markets = response
        for i in range(0, len(markets)):
            market = markets[i]
            id = market['id']
            baseId = market['base_currency']
            quoteId = market['quote_currency']
            base = baseId.upper()
            base = self.common_currency_code(base)
            quote = quoteId.upper()
            quote = self.common_currency_code(quote)
            symbol = base + '/' + quote
            precision = {
                'price': market['price_max_precision'],
                'amount': 8,
            }
            limits = {
                'price': {
                    'min': market['quote_increment'],
                    'max': None,
                },
                'amount': {
                    'min': market['base_min_size'],
                    'max': market['base_max_size'],
                },
            }
            if symbol in self.options['limits']:
                limits = self.extend(self.options['limits'][symbol], limits)
            result.append({
                'id': id,
                'symbol': symbol,
                'base': base,
                'quote': quote,
                'baseId': baseId,
                'quoteId': quoteId,
                'active': True,
                'precision': precision,
                'limits': limits,
                'info': market,
            })
        return result

    async def fetch_balance(self, params={}):
        await self.load_markets()
        response = await self.privateGetWallet(params)
        result = {'info': response}
        balances = response
        for i in range(0, len(balances)):
            balance = balances[i]
            currencyId = balance['id']
            code = currencyId.upper()
            if currencyId in self.currencies_by_id:
                code = self.currencies_by_id[currencyId]['code']
            else:
                code = self.common_currency_code(code)
            account = self.account()
            account['free'] = float(balance['available'])
            account['used'] = float(balance['frozen'])
            account['total'] = self.sum(account['free'], account['used'])
            result[code] = account
        return result

    def parse_bids_asks(self, orders, priceKey=0, amountKey=1):
        result = []
        length = len(orders)
        halfLength = int(length / 2)
        # += 2 in the for loop below won't transpile
        for i in range(0, halfLength):
            index = i * 2
            priceField = self.sum(index, priceKey)
            amountField = self.sum(index, amountKey)
            result.append([
                orders[priceField],
                orders[amountField],
            ])
        return result

    async def fetch_order_book(self, symbol=None, limit=None, params={}):
        await self.load_markets()
        if limit is not None:
            if (limit == 20) or (limit == 100):
                limit = 'L' + str(limit)
            else:
                raise ExchangeError(self.id + ' fetchOrderBook supports limit of 20, 100 or no limit. Other values are not accepted')
        else:
            limit = 'full'
        request = self.extend({
            'symbol': self.market_id(symbol),
            'level': limit,  # L20, L100, full
        }, params)
        response = await self.marketGetDepthLevelSymbol(request)
        orderbook = response['data']
        return self.parse_order_book(orderbook, orderbook['ts'], 'bids', 'asks', 0, 1)

    async def fetch_ticker(self, symbol, params={}):
        await self.load_markets()
        market = self.market(symbol)
        ticker = await self.marketGetTickerSymbol(self.extend({
            'symbol': market['id'],
        }, params))
        return self.parse_ticker(ticker['data'], market)

    def parse_ticker(self, ticker, market=None):
        timestamp = None
        symbol = None
        if market is None:
            tickerType = self.safe_string(ticker, 'type')
            if tickerType is not None:
                parts = tickerType.split('.')
                id = parts[1]
                if id in self.markets_by_id:
                    market = self.markets_by_id[id]
        values = ticker['ticker']
        last = values[0]
        if market is not None:
            symbol = market['symbol']
        return {
            'symbol': symbol,
            'timestamp': timestamp,
            'datetime': self.iso8601(timestamp),
            'high': values[7],
            'low': values[8],
            'bid': values[2],
            'bidVolume': values[3],
            'ask': values[4],
            'askVolume': values[5],
            'vwap': None,
            'open': None,
            'close': last,
            'last': last,
            'previousClose': None,
            'change': None,
            'percentage': None,
            'average': None,
            'baseVolume': values[9],
            'quoteVolume': values[10],
            'info': ticker,
        }

    def parse_trade(self, trade, market=None):
        symbol = None
        if market is not None:
            symbol = market['symbol']
        timestamp = int(trade['ts'])
        side = trade['side'].lower()
        orderId = self.safe_string(trade, 'id')
        price = self.safe_float(trade, 'price')
        amount = self.safe_float(trade, 'amount')
        cost = price * amount
        fee = None
        return {
            'id': orderId,
            'info': trade,
            'timestamp': timestamp,
            'datetime': self.iso8601(timestamp),
            'symbol': symbol,
            'type': None,
            'order': orderId,
            'side': side,
            'price': price,
            'amount': amount,
            'cost': cost,
            'fee': fee,
        }

    async def fetch_trades(self, symbol, since=None, limit=50, params={}):
        await self.load_markets()
        market = self.market(symbol)
        request = {
            'symbol': market['id'],
            'limit': limit,
        }
        if since is not None:
            request['timestamp'] = int(since / 1000)
        response = await self.marketGetTradesSymbol(self.extend(request, params))
        return self.parse_trades(response['data'], market, since, limit)

    async def create_order(self, symbol, type, side, amount, price=None, params={}):
        await self.load_markets()
        request = {
            'symbol': self.market_id(symbol),
            'amount': self.amount_to_precision(symbol, amount),
            'side': side,
        }
        if type == 'limit':
            request['price'] = self.price_to_precision(symbol, price)
        result = await self.privatePostOrders(self.extend(request, params))
        return {
            'info': result,
            'id': result['entrust_id'],
        }

    async def cancel_order(self, id, symbol=None, params={}):
        response = await self.privateDeleteOrdersOrderId(self.extend({
            'order_id': id,
            'entrust_id': id,
        }, params))
        return response

    async def cancel_orders(self, symbol, side, params={}):
        await self.load_markets()
        market = self.market(symbol)
        request = {
            'symbol': market['id'],
            'side': side,
        }
        response = await self.privateDeleteOrders(self.extend(request, params))
        return response

    def parse_order_status(self, status):
        statuses = {
            '1': 'open',
            '2': 'partial_filled',
            '3': 'closed',
            '4': 'canceled',
            '5': 'partial_filled',
            '6': 'partial_canceled',
        }
        if status in statuses:
            return statuses[status]
        return status

    def parse_order(self, order, market=None):
        id = self.safe_string(order, 'entrust_id')
        side = self.safe_string(order, 'side')
        status = self.parse_order_status(self.safe_string(order, 'status'))
        symbol = None
        if market is None:
            marketId = self.safe_string(order, 'symbol')
            if marketId in self.markets_by_id:
                market = self.markets_by_id[marketId]
        timestamp = self.safe_integer(order, 'timestamp')
        amount = self.safe_float(order, 'original_amount')
        filled = self.safe_float(order, 'executed_amount')
        remaining = None
        price = self.safe_float(order, 'price')
        cost = None
        if filled is not None:
            if amount is not None:
                remaining = amount - filled
            if price is not None:
                cost = price * filled
        feeCurrency = None
        if market is not None:
            symbol = market['symbol']
            feeCurrency = market['base'] if (side == 'buy') else market['quote']
        feeCost = self.safe_float(order, 'fees')
        result = {
            'info': order,
            'id': id,
            'timestamp': timestamp,
            'datetime': self.iso8601(timestamp),
            'lastTradeTimestamp': None,
            'symbol': symbol,
            'type': 'limit',
            'side': side,
            'price': price,
            'cost': cost,
            'amount': amount,
            'remaining': remaining,
            'filled': filled,
            'average': None,
            'status': status,
            'fee': {
                'cost': feeCost,
                'currency': feeCurrency,
            },
            'trades': None,
        }
        return result

    async def fetch_order(self, id, symbol=None, params={}):
        await self.load_markets()
        request = self.extend({
            'order_id': id,
        }, params)
        response = await self.privateGetOrdersOrderId(request)
        return self.parse_order(response['data'])

    async def fetch_open_orders(self, symbol=None, since=None, limit=None, params={}):
        result = await self.fetch_orders(symbol, since, limit, {'states': 'submitted,partial_filled'})
        return result

    async def fetch_closed_orders(self, symbol=None, since=None, limit=None, params={}):
        result = await self.fetch_orders(symbol, since, limit, {'states': 'filled'})
        return result

    async def fetch_orders(self, symbol=None, since=None, limit=None, params={}):
        await self.load_markets()
        market = self.market(symbol)
        request = {
            'symbol': market['id'],
            'states': 0,
        }
        if limit is not None:
            request['limit'] = limit
        if since is not None:
            request['offset'] = since
        response = await self.privateGetOrders(self.extend(request, params))
        return self.parse_orders(response['orders'], market, since, limit)

    def parse_ohlcv(self, ohlcv, market=None, timeframe='1m', since=None, limit=None):
        return [
            ohlcv['id'] * 1000,
            ohlcv['open'],
            ohlcv['high'],
            ohlcv['low'],
            ohlcv['close'],
            ohlcv['base_vol'],
        ]

    async def fetch_ohlcv(self, symbol, timeframe='1m', since=None, limit=100, params={}):
        await self.load_markets()
        if limit is None:
            raise ExchangeError(self.id + ' fetchOHLCV requires a limit argument')
        market = self.market(symbol)
        request = self.extend({
            'symbol': market['id'],
            'timeframe': self.timeframes[timeframe],
            'limit': limit,
        }, params)
        response = await self.marketGetCandlesTimeframeSymbol(request)
        return self.parse_ohlcvs(response['data'], market, timeframe, since, limit)

    async def fetch_token(self):
        tokendata = {
            'grant_type': 'client_credentials',
            'client_id': self.apiKey,
            'client_secret': self.hmac(self.apiKey + ':' + self.secret + ':' + self.memo, self.encode(self.secret), hashlib.sha256),
        }
        responses = self.publicPostAuthentication(tokendata)
        self.accesstoken = responses['access_token']

    def nonce(self):
        return self.milliseconds()

    def sign(self, path, api='public', method='GET', params={}, headers=None, body=None):
        request = '/' + self.version + '/'
        request += self.implode_params(path, params)
        query = self.omit(params, self.extract_params(path))
        url = self.urls['api'] + request
        if (api == 'public'):
            if query:
                if method == 'GET' or method == 'DELETE':
                    url += '?' + self.rawencode(query)
                elif method == 'POST':
                    body = self.urlencode(query)
        elif api == 'private':
            self.fetch_token()
            self.check_required_credentials()
            timestamp = str(self.nonce())
            signature = ''
            if query:
                query = self.keysort(query)
                if method == 'GET' or method == 'DELETE':
                    url += '?' + self.rawencode(query)
                body = self.urlencode(query)
                signature = self.hmac(body, self.encode(self.secret), hashlib.sha256)
            headers = {
                'X-BM-AUTHORIZATION': 'Bearer ' + self.accesstoken,
                'X-BM-TIMESTAMP': timestamp,
                'X-BM-SIGNATURE': signature,
                'Content-Type': 'application/json',
            }
        return {'url': url, 'method': method, 'body': body, 'headers': headers}

    def handle_errors(self, code, reason, url, method, headers, body):
        if not isinstance(body, basestring):
            return  # fallback to default error handler
        if len(body) < 2:
            return  # fallback to default error handler
        if (body[0] == '{') or (body[0] == '['):
            response = json.loads(body)
            status = self.safe_string(response, 'message')
            if status is not None:
                feedback = self.id + ' ' + body
                if status in self.exceptions:
                    exceptions = self.exceptions
                    raise exceptions[status](feedback)
                raise ExchangeError(feedback)
