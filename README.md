# JSON:API Resources for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/larsmbergvall/json-api-resources-for-laravel.svg?style=flat-square)](https://packagist.org/packages/larsmbergvall/json-api-resources-for-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/larsmbergvall/json-api-resources-for-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/larsmbergvall/json-api-resources-for-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/larsmbergvall/json-api-resources-for-laravel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/larsmbergvall/json-api-resources-for-laravel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/larsmbergvall/json-api-resources-for-laravel.svg?style=flat-square)](https://packagist.org/packages/larsmbergvall/json-api-resources-for-laravel)

## Example
This is an example of the output generated by this package:
```php
    public function books()
    {
        $books = Book::with(['author.country'])->take(2)->get();

        return JsonApiResourceCollection::make($books)->withIncluded();
    }
```

Returns this:

```json
{
	"data": [
		{
			"id": "1",
			"type": "books",
			"attributes": {
				"title": "Dolores Dolor Totam",
				"year": 1871
			},
			"relationships": {
				"author": {
					"data": {
						"id": "1",
						"type": "author"
					}
				}
			},
			"links": {},
			"meta": {}
		},
		{
			"id": "2",
			"type": "books",
			"attributes": {
				"title": "Velit Quo Omnis",
				"year": 1969
			},
			"relationships": {
				"author": {
					"data": {
						"id": "2",
						"type": "author"
					}
				}
			},
			"links": {},
			"meta": {}
		}
	],
	"included": [
		{
			"id": "1",
			"type": "author",
			"attributes": {
				"name": "Rylee Heller DDS",
				"country_id": 18,
				"bio": "Non iusto porro maxime. Quibusdam provident architecto magni sed id et. Voluptatem sint a numquam eius omnis aut.\n\nVoluptas quis voluptatem et fuga aspernatur eaque. Necessitatibus possimus iure corporis et quasi cum. Blanditiis quis sapiente ut dolores. Dolorem odio et quas aut et nihil qui.\n\nAut quod sint numquam. Ullam id odio velit neque non. Aut recusandae sint explicabo ut veritatis aliquid harum.",
				"born_at": "1986-10-22 08:00:02",
				"created_at": "2023-03-09 12:55:20",
				"updated_at": "2023-03-09 12:55:20"
			},
			"relationships": {},
			"links": {},
			"meta": {}
		},
		{
			"id": "2",
			"type": "author",
			"attributes": {
				"name": "Ms. Rosella Quigley Sr.",
				"country_id": 12,
				"bio": "Architecto voluptas nobis sed. Ea qui quas voluptatem est nisi. Voluptatem et quis ut asperiores fuga autem blanditiis repellendus. Qui non sapiente esse quasi corporis fugit ut aut.\n\nDolorem id dolore quis qui ullam dolorem. Qui voluptatibus quia reprehenderit dolor aut est corrupti. Debitis debitis cum vitae nam quis fugit nemo. Quia labore et delectus est qui.\n\nNumquam accusantium et in. Non tenetur iure explicabo expedita sint. Quam sit qui doloremque asperiores ducimus impedit enim. In recusandae et cumque nisi ea laudantium in.",
				"born_at": "1996-08-07 13:15:36",
				"created_at": "2023-03-09 12:55:20",
				"updated_at": "2023-03-09 12:55:20"
			},
			"relationships": {},
			"links": {},
			"meta": {}
		}
	]
}
```

## Missing/upcoming features
* Errors: There is no good way of returning errors at the moment. It will be implemented in the future.
* Probably some more stuff!

## Installation

You can install the package via composer:

```bash
composer require larsmbergvall/json-api-resources-for-laravel
```

## Usage

### Attributes

This package uses PHP 8 attributes to annotate Eloquent models. There is no need for additional resource classes.
Attributes are used like this:

```php
use Larsmbergvall\JsonApiResourcesForLaravel\Attributes\JsonApiIncludeAttributes;
use Larsmbergvall\JsonApiResourcesForLaravel\Attributes\JsonApiIncludeRelationships;
use Larsmbergvall\JsonApiResourcesForLaravel\Attributes\JsonApiType;

#[JsonApiType('books')] // Change `type` property
#[JsonApiIncludeAttributes(['title', 'isbn', 'year'])] // Which of the models attributes to include
 // Which of the models relationships to include.
 // NOTE: it only includes eager loaded relationships regardless of this attribute
#[JsonApiIncludeRelationships(['author'])]
class Book extends Model
{
    // ...
}
```

#### `#[JsonApiType(string)]`

This attribute can be used to change the value of the `type` property in the final output json. This attribute is
optional. If it is not used, `type` will be set to the singular version of the models name, i.e. `'type' => 'book'`.

#### `#[JsonApiIncludeAttributes(string[])]`

This attribute can be used to specify which of the models attributes should be included in the final output json. This
attribute is optional. If it is not used, all visible model attributes will be included. It
uses `$model->attributesToArray()` to get the attributes, so it will respect any `$hidden` fields.

#### `#[JsonApiIncludeRelationships(string[])]`

This attribute can be used to specify which of the models relationships should be included. This attribute is optional.
If it is not used, no relationships will be included in the final output json.

**NOTE:** Regardless of what is included by this attribute, `JsonApiResource` only includes eager loaded relationships.

### Creating JsonApiResources

If you have a single model, you should use `JsonApiResource`: 

```php
use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiResource;

public function show(Book $book): JsonApiResource {
    return jsonApiResponse(JsonApiResource::make($book));
}
```

If you have a collection of models, you should use `JsonApiResourceCollection`:

```php
use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiResourceCollection;

public function index(): JsonApiResource {
    // Can also be used with pagination:
    // return JsonApiResourceCollection::make(Book::orderBy('title')->paginate());
    
    return jsonApiResponse(JsonApiResourceCollection::make(Book::all()));
}
```

Regardless, you should always return your json api resources wrapped in a `JsonApiResponse` (which is pretty much just a
regular response but with
the correct Content-Type header for JSON:API). A simple way of doing that is using the global helper:

```php
return jsonApiResponse($content, $status, $headers)
```
## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Lars Bergvall](https://github.com/larsmbergvall)
- [All Contributors](../../contributors)
- [Spatie](https://spatie.be/) for the amazing Laravel package skeleton

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
