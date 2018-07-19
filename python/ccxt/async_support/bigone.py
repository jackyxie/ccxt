# -*- coding: utf-8 -*-

# PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
# https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

from ccxt.async_support.base.exchange import Exchange
import math
from ccxt.base.errors import ExchangeError
from ccxt.base.errors import AuthenticationError
from ccxt.base.errors import InsufficientFunds
from ccxt.base.errors import InvalidOrder


class bigone (Exchange):

    def describe(self):
        return self.deep_extend(super(bigone, self).describe(), {
            'id': 'bigone',
            'name': 'BigONE',
            'countries': 'GB',
            'version': 'v2',
            'has': {
                'fetchTickers': True,
                'fetchOpenOrders': True,
                'fetchOrders': True,
                'fetchMyTrades': True,
                'fetchDepositAddress': True,
                'withdraw': True,
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
                # see https://open.big.one/docs/api_error_codes.html
                '10001': ExchangeError,  # "syntax error"
                '10002': ExchangeError,  # "cannot query fields"
                '10003': ExchangeError,  # "service timeout"
                '10004': ExchangeError,  # "response error"
                '10005': ExchangeError,  # "internal error"
                '10006': ExchangeError,  # "invalid credentials"
                '10007': ExchangeError,  # "params error"
                '10008': ExchangeError,  # "invalid otp"
                '10009': ExchangeError,  # "invalid asset pin"
                '10010': ExchangeError,  # "email or password wrong"
                '10011': ExchangeError,  # "system error"
                '10012': ExchangeError,  # "invalid password reset token"
                '10013': ExchangeError,  # "resouce not found"
                '10014': ExchangeError,  # "Current broker does not support password auth."
                '10015': ExchangeError,  # "Current broker does not support cookie auth."
                '10016': ExchangeError,  # "broker not support login with third-party authentication"
                '10017': ExchangeError,  # "favourite broker market not existed"
                '10018': AuthenticationError,  # "invalid token"
                '10019': ExchangeError,  # "failed to create token"
                '10022': ExchangeError,  # "invalid auth schema"
                '10023': ExchangeError,  # "unauthenticated"
                '10024': ExchangeError,  # "invalid otp secret"
                '10025': ExchangeError,  # "missing otp code"
                '10026': ExchangeError,  # "invalid asset pin reset token"
                '10027': ExchangeError,  # "invalid verification state"
                '10028': ExchangeError,  # "invalid otp reset token"
                '30000': ExchangeError,  # "Unknown Error"
                '30001': ExchangeError,  # "Unknown Asset"
                '30002': ExchangeError,  # "Venezia Error"
                '30003': ExchangeError,  # "Invalid Field"
                '30004': InsufficientFunds,  # "Insufficient Balance"
                '30005': ExchangeError,  # "Perimission Denied"
                '30006': ExchangeError,  # "You are not credible enough to create withdrawal."
                '30007': ExchangeError,  # "Current broker does not support admin withdrawal now."
                '30008': ExchangeError,  # "Memo Not Found"
                '30009': ExchangeError,  # "Withdraw Suspended"
                '40001': ExchangeError,  # "Market not found"
                '40002': InvalidOrder,  # "Price too low"
                '40003': InvalidOrder,  # "Amount too low"
                '40004': InvalidOrder,  # "Filled amount too large"
                '40000': ExchangeError,  # "Unknown Error"
            },
            'fees': {
                'trading': {
                    'maker': 0.1 / 100,
                    'taker': 0.1 / 100,
                },
                'funding': {
                    # HARDCODING IS DEPRECATED THE FEES BELOW ARE TO BE REMOVED SOON
                    'withdraw': {
                        'BTC': 0.002,
                        'ETH': 0.01,
                        'EOS': 0.01,
                        'ZEC': 0.002,
                        'LTC': 0.01,
                        'QTUM': 0.01,
                        # 'INK': 0.01 QTUM,
                        # 'BOT': 0.01 QTUM,
                        'ETC': 0.01,
                        'GAS': 0.0,
                        'BTS': 1.0,
                        'GXS': 0.1,
                        'BITCNY': 1.0,
                    },
                },
            },
        })

    async def fetch_markets(self):
        response = await self.publicGetMarkets()
        markets = response['data']
        result = []
        for i in range(0, len(markets)):
            market = markets[i]
            id = market['name']
            baseId = market['baseAsset']['symbol']
            quoteId = market['quoteAsset']['symbol']
            base = self.common_currency_code(baseId)
            quote = self.common_currency_code(quoteId)
            symbol = base + '/' + quote
            precision = {
                'amount': 8,
                'price': 8,
            }
            result.append({
                'id': id,
                'symbol': symbol,
                'base': base,
                'quote': quote,
                'baseId': baseId,
                'quoteId': quoteId,
                'active': True,
                'precision': precision,
                'limits': {
                    'amount': {
                        'min': math.pow(10, -market['baseScale']),
                        'max': None,
                    },
                    'price': {
                        'min': math.pow(10, -market['quoteScale']),
                        'max': None,
                    },
                    'cost': {
                        'min': None,
                        'max': None,
                    },
                },
                'info': market,
            })
        return result

    def parse_ticker(self, ticker, market=None):
        #
        #     [
        #         {
        #             "volume": "190.4925000000000000",
        #             "open": "0.0777371200000000",
        #             "market_uuid": "38dd30bf-76c2-4777-ae2a-a3222433eef3",
        #             "market_id": "ETH-BTC",
        #             "low": "0.0742925600000000",
        #             "high": "0.0789150000000000",
        #             "daily_change_perc": "-0.3789180767180466680525339760",
        #             "daily_change": "-0.0002945600000000",
        #             "close": "0.0774425600000000",  # last price
        #             "bid": {
        #                 "price": "0.0764777900000000",
        #                 "amount": "6.4248000000000000"
        #             },
        #             "ask": {
        #                 "price": "0.0774425600000000",
        #                 "amount": "1.1741000000000000"
        #             }
        #         }
        #     ]
        #
        if market is not None:
            marketId = self.safe_string(ticker, 'market_id')
            if marketId in self.markets_by_id:
                market = self.markets_by_id[marketId]
        symbol = None
        if market is not None:
            symbol = market['symbol']
        timestamp = self.milliseconds()
        close = self.safe_float(ticker, 'close')
        return {
            'symbol': symbol,
            'timestamp': timestamp,
            'datetime': self.iso8601(timestamp),
            'high': self.safe_float(ticker, 'high'),
            'low': self.safe_float(ticker, 'low'),
            'bid': self.safe_float(ticker['bid'], 'price'),
            'bidVolume': self.safe_float(ticker['bid'], 'amount'),
            'ask': self.safe_float(ticker['ask'], 'price'),
            'askVolume': self.safe_float(ticker['ask'], 'amount'),
            'vwap': None,
            'open': self.safe_float(ticker, 'open'),
            'close': close,
            'last': close,
            'previousDayClose': None,
            'change': self.safe_float(ticker, 'daily_change'),
            'percentage': self.safe_float(ticker, 'daily_change_perc'),
            'average': None,
            'baseVolume': self.safe_float(ticker, 'volume'),
            'quoteVolume': None,
            'info': ticker,
        }

    async def fetch_ticker(self, symbol, params={}):
        await self.load_markets()
        market = self.market(symbol)
        response = await self.publicGetMarketsSymbol(self.extend({
            'symbol': market['id'],
        }, params))
        return self.parse_ticker(response['data']['ticker'], market)

    async def fetch_tickers(self, symbols=None, params={}):
        await self.load_markets()
        response = await self.publicGetMarkets(params)
        tickers = response['data']
        result = {}
        for i in range(0, len(tickers)):
            ticker = self.parse_ticker(tickers[i])
            symbol = ticker['symbol']
            result[symbol] = ticker
        return result

    async def fetch_order_book(self, symbol, params={}):
        await self.load_markets()
        response = await self.publicGetMarketsSymbolBook(self.extend({
            'symbol': self.market_id(symbol),
        }, params))
        return self.parse_order_book(response['data'], None, 'bids', 'asks', 'price', 'amount')

    async def fetch_orders(self, symbol=None, since=None, limit=None, params={}):
        await self.load_markets()
        market = self.market(symbol)
        request = {
            'market_id': market['id'],
        }
        response = await self.privateGetViewerOrders(self.extend(request, params))
        return self.parse_orders(response['data']['edges'], market, since, limit)

    def parse_trade(self, trade, market=None):
        timestamp = self.parse8601(trade['created_at'])
        price = float(trade['price'])
        amount = float(trade['amount'])
        symbol = market['symbol']
        cost = self.cost_to_precision(symbol, price * amount)
        side = trade['trade_side'] == 'sell' if 'ASK' else 'buy'
        return {
            'timestamp': timestamp,
            'datetime': self.iso8601(timestamp),
            'symbol': symbol,
            'id': self.safe_string(trade, 'trade_id'),
            'order': None,
            'type': 'limit',
            'side': side,
            'price': price,
            'amount': amount,
            'cost': float(cost),
            'fee': None,
            'info': trade,
        }

    async def fetch_trades(self, symbol, since=None, limit=None, params={}):
        await self.load_markets()
        market = self.market(symbol)
        response = await self.publicGetMarketsSymbolTrades(self.extend({
            'symbol': market['id'],
        }, params))
        return self.parse_trades(response['data'], market, since, limit)

    async def fetch_balance(self, params={}):
        await self.load_markets()
        response = await self.privateGetViewerAccounts(params)
        result = {'info': response}
        balances = response['data']
        for i in range(0, len(balances)):
            balance = balances[i]
            id = balance['asset_id']
            currency = self.common_currency_code(id)
            account = {
                'free': float(balance['balance']),
                'used': float(balance['locked_balance']),
                'total': 0.0,
            }
            account['total'] = self.sum(account['free'], account['used'])
            result[currency] = account
        return self.parse_balance(result)

    def parse_order(self, order, market=None):
        order_data = self.safe_value(order, 'node')
        if order_data:
            order = order['node']
        marketId = self.safe_string(order, 'market_id')
        symbol = None
        if marketId and not market and(marketId in list(self.marketsById.keys())):
            market = self.marketsById[marketId]
        if market:
            symbol = market['symbol']
        timestamp = self.parse8601(order['inserted_at'])
        price = float(order['price'])
        amount = self.safe_float(order, 'amount')
        filled = self.safe_float(order, 'filled_amount')
        remaining = amount - filled
        status = self.parse_order_status(order['state'])
        if status == 'filled':
            status = 'closed'
        side = self.safe_integer(order, 'side')
        if side == 'BID':
            side = 'buy'
        else:
            side = 'sell'
        return {
            'id': self.safe_string(order, 'id'),
            'datetime': self.iso8601(timestamp),
            'timestamp': timestamp,
            'status': status,
            'symbol': symbol,
            'type': 'limit',
            'side': side,
            'price': price,
            'cost': None,
            'amount': amount,
            'filled': filled,
            'remaining': remaining,
            'trades': None,
            'fee': None,
            'info': order,
        }

    def parse_order_status(self, status):
        statuses = {
            'CANCELED': 'canceled',
            'PENDING': 'open',
            'FILLED': 'closed',
        }
        if status in statuses:
            return statuses[status]
        return status

    async def create_order(self, symbol, type, side, amount, price=None, params={}):
        await self.load_markets()
        market = self.market(symbol)
        response = await self.privatePostViewerOrders(self.extend({
            'market_id': market['info']['uuid'],
            'side': (side == 'BID' if 'buy' else 'ASK'),
            'amount': amount,
            'price': price,
        }, params))
        # TODO: what's the actual response here
        return response['data']

    async def cancel_order(self, id, symbol=None, params={}):
        response = await self.privatePostViewerOrdersOrderIdCancel(self.extend({
            'order_id': id,
        }, params))
        return response

    async def cancel_orders(self, symbol=None, params={}):
        request = {}
        if symbol is not None:
            await self.load_markets()
            market = self.market(symbol)
            request['market_id'] = market['info']['uuid']
        #
        # the caching part to be removed
        #
        #     response = await self.privatePostOrderCancelAll(self.extend(request, params))
        #     openOrders = self.filter_by(self.orders, 'status', 'open')
        #     for i in range(0, len(openOrders)):
        #         order = openOrders[i]
        #         orderId = order['id']
        #         self.orders[orderId]['status'] = 'canceled'
        #     }
        #     return response
        #
        return await self.privatePostViewerOrdersCancelAll(self.extend(request, params))

    async def fetch_order(self, id, symbol=None, params={}):
        await self.load_markets()
        response = await self.privateGetOrdersId(self.extend({
            'id': id,
        }, params))
        return self.parse_order(response['data'])

    async def fetch_open_orders(self, symbol=None, since=None, limit=None, params={}):
        await self.load_markets()
        market = self.market(symbol)
        # TODO: check if it's for open orders only
        request = {
            'market': market['id'],
        }
        if limit:
            request['limit'] = limit
        response = await self.privateGetOrders(self.extend(request, params))
        return self.parse_orders(response['data'], market, since, limit)

    async def fetch_my_trades(self, symbol=None, since=None, limit=None, params={}):
        await self.load_markets()
        market = self.market(symbol)
        request = {
            'market': market['id'],
        }
        if limit:
            request['limit'] = limit
        response = await self.privateGetTrades(self.extend(request, params))
        trades = response['data']['trade_history']
        return self.parse_trades(trades, market, since, limit)

    async def fetch_deposit_address(self, code, params={}):
        await self.load_markets()
        currency = self.currency(code)
        response = await self.privateGetAccountsCurrency(self.extend({
            'currency': currency['id'],
        }, params))
        address = self.safe_string(response['data'], 'public_key')
        status = 'ok' if address else 'none'
        return {
            'currency': code,
            'address': address,
            'status': status,
            'info': response,
        }

    async def withdraw(self, code, amount, address, tag=None, params={}):
        await self.load_markets()
        currency = self.currency(code)
        request = {
            'withdrawal_type': currency['id'],
            'address': address,
            'amount': amount,
            # 'fee': 0.0,
            # 'asset_pin': 'YOUR_ASSET_PIN',
        }
        if tag:
            # probably it's not the same
            request['label'] = tag
        response = await self.privatePostWithdrawals(self.extend(request, params))
        return {
            'id': None,
            'info': response,
        }

    def sign(self, path, api='public', method='GET', params={}, headers=None, body=None):
        query = self.omit(params, self.extract_params(path))
        url = self.urls['api'] + '/' + self.version + '/' + self.implode_params(path, params)
        if api == 'public':
            if query:
                url += '?' + self.urlencode(query)
        else:
            self.check_required_credentials()
            nonce = self.nonce()
            request = {
                'type': 'OpenAPI',
                'sub': self.apiKey,
                'nonce': nonce * 1000000000,
            }
            headers['Authorization'] = 'Bearer ' + self.jwt(request, self.secret)
            if method == 'GET':
                if query:
                    url += '?' + self.urlencode(query)
            elif method == 'POST':
                headers['Content-Type'] = 'application/json'
                body = self.json(query)
        return {'url': url, 'method': method, 'body': body, 'headers': headers}

    async def request(self, path, api='public', method='GET', params={}, headers=None, body=None):
        response = await self.fetch2(path, api, method, params, headers, body)
        error = self.safe_value(response, 'error')
        data = self.safe_value(response, 'data')
        if error or data is None:
            code = self.safe_integer(error, 'code')
            errorClasses = {
                '401': AuthenticationError,
            }
            message = self.safe_string(error, 'description', 'Error')
            ErrorClass = self.safe_string(errorClasses, code, ExchangeError)
            raise ErrorClass(message)
        return response
