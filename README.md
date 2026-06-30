# Feedico API — PHP example

> Pull **affiliate merchants** and **coupon codes** from [Feedico](https://feedico.io) in a few lines of PHP. No framework, no Composer — just cURL and JSON.

**Website:** [feedico.io](https://feedico.io) · **Documentation:** [feedico.io/docs](https://feedico.io/docs)

`feedico` · `coupon-api` · `affiliate-api` · `merchants` · `coupons` · `rest-api` · `php` · `api-example`

---

## What this repo is

A copy-paste-friendly starter for publishers who want to:

- list merchants (affiliate networks / stores) from your Feedico account
- list coupons with optional filters (network provider, merchant name)
- paginate through large catalogs

It mirrors the same REST calls used by the official [Feedico Sync WordPress plugin](https://github.com/feedico-io/feedico-wp-plugin).

---

## 30-second start

```bash
git clone https://github.com/feedico-io/feedico-api-php-example.git
cd feedico-api-php-example
cp .env.example .env
# Edit .env — paste your fdco_… Bearer token from the Feedico dashboard

php examples/merchants.php
php examples/coupons.php
```

**Requirements:** PHP 7.4+ with the `curl` extension.

---

## Get your API token

1. Sign in at **[feedico.io](https://feedico.io)**
2. Open your dashboard and copy the **Bearer token** (`fdco_…`)
3. Put it in `.env` as `FEEDICO_API_TOKEN`

Full auth and endpoint details: **[feedico.io/docs](https://feedico.io/docs)** · live OpenAPI on [api.feedico.io](https://api.feedico.io).

---

## API endpoints used

| Resource | Method | URL |
|----------|--------|-----|
| Merchants (networks) | `POST` | `https://api.feedico.io/api/v1/me/networks` |
| Coupons | `POST` | `https://api.feedico.io/api/v1/me/coupons` |

Both requests send JSON:

```json
{
  "page": 1,
  "pageSize": 50,
  "provider": "cj",
  "firmName": ""
}
```

Set `provider` to `null` or leave empty to fetch across networks. Use `firmName` to search by store name.

**Headers:**

```
Authorization: Bearer fdco_your_token
Content-Type: application/json
Accept: application/json
```

---

## Project layout

```
src/FeedicoClient.php   # tiny reusable client
src/env.php             # .env loader (no dependencies)
examples/merchants.php  # print merchant rows
examples/coupons.php    # print coupon rows
```

Use `FeedicoClient::extractRows()` to normalize list payloads (`networks`, `coupons`, `items`, or a plain array).

---

## Pagination loop

```php
$client = new FeedicoClient(getenv('FEEDICO_API_TOKEN'));
$page = 1;

do {
    $payload = $client->listCoupons($page, 100, null, null);
    $rows = FeedicoClient::extractRows($payload);
    foreach ($rows as $coupon) {
        // save to DB, cache, etc.
    }
    $page++;
} while (count($rows) === 100);
```

---

## Related projects

| Repo | Language |
|------|----------|
| **This repo** | PHP |
| [feedico-api-python-example](https://github.com/feedico-io/feedico-api-python-example) | Python |
| [feedico-api-node-example](https://github.com/feedico-io/feedico-api-node-example) | Node.js |
| [feedico-api-csharp-example](https://github.com/feedico-io/feedico-api-csharp-example) | C# / .NET |
| [feedico-api-postman](https://github.com/feedico-io/feedico-api-postman) | Postman |
| [feedico-etl-starter](https://github.com/feedico-io/feedico-etl-starter) | ETL |
| [feedico-wp-plugin](https://github.com/feedico-io/feedico-wp-plugin) | WordPress plugin |

---

## License

MIT — use freely in your own apps and tutorials.

Questions? [feedico.io](https://feedico.io) · [Documentation hub](https://feedico.io/docs)
