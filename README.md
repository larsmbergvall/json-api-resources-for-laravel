# JSON:API Resources for Laravel

## Table of Contents
* [Example](#example)
* [Installation](#installation)
* [Usage](#usage)
* [Attributes](#attributes)
  * [JsonApiType](#jsonapitypestring)
  * [JsonApiIncludeAttributes](#jsonapiincludeattributesstring)
  * [JsonApiIncludeRelationships](#jsonapiincluderelationshipsstring)
* [Creating JsonApiResources](#creating-jsonapiresources)
* [Middleware](#middleware)
* [The JsonApiResource class](#the-jsonapiresource-class)
* [The JsonApiResourceCollection class](#the-jsonapiresourcecollection-class)
* [Error transformation](#error-transformation)
* [Testing](#testing)
* [Changelog](#changelog)
* [Credits](#credits)
* [License](#license)

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

## Installation

You can install the package via composer:

```bash
composer require larsmbergvall/json-api-resources-for-laravel
```

**It is recommended** to use the middleware, either on all API calls:

```php
// ./app/Http/Kernel.php

protected $middlewareGroups = [
    // ...
    // Web middleware and such
    // ...
    'api' => [
        // Other api middlewares
        JsonApiMiddleware::class,
    ],
]
```

Or, on a per-route basis:
```php
// ./routes/api.php

Route::prefix('/v1')
    ->middleware(JsonApiMiddleware::class)
    ->group(function () {
        // Api v1 routes
    });

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

public function show(Book $book): JsonApiResource
{
    return JsonApiResource::make($book);
}
```

If you have a collection of models, you should use `JsonApiResourceCollection`:

```php
use Larsmbergvall\JsonApiResourcesForLaravel\JsonApi\JsonApiResourceCollection;

public function index(): JsonApiResource
{
    // Can also be used with pagination:
    // return JsonApiResourceCollection::make(Book::orderBy('title')->paginate());
    
    return JsonApiResourceCollection::make(Book::all());
}
```

If you need to manually return a JSON:API response, or don't want to use the middleware, you can use
the `jsonApiResponse()`-helper:

```php
// return jsonApiResponse(JsonApiResource::make($book));
return jsonApiResponse($content, $status, $headers)
```

It sets the correct `Content-Type` header for JSON:API responses. Note that it doesn't transform validation errors.

## Middleware

The middleware primarily takes care of two things:
1. Making sure the response that is returned has the correct headers etc.
2. Transforming Laravels validation errors into the expected JSON:API error format

The middleware should be executed *after* any other middlewares that could change the `Content-Type` header of the
response.

## The JsonApiResource class

This class has a few public methods that you can use. Most likely you will not need to, because
the `JsonApiResourceCollection` class handles that stuff automatically.

However, there is an important method: `withIncluded()`. By default, a `JsonApiResource` does not include it's related
data. If you however run `$resource->withIncluded()` (or `JsonApiResource::make($book)->withIncluded())` it will do so.

**NOTE:** It only includes eager loaded relationships, so if you need to exclude something for a certain api route you
can just choose not to load a relationship. However, it includes related data recursively, meaning that it also
includes data related to the included data.

## The JsonApiResourceCollection class

This class also has the `withIncluded()` method that you can use to include any related data to your resources. Just as
with the `JsonApiResource`, it only includes relations that are already loaded. However, it includes related data
recursively, meaning that it also includes data related to the included data.

### Error transformation

When validation fails in Laravel, normally you get a response that looks like this:
```json
{
  "message": "The data.attributes.foo field is required.",
  "errors": {
    "data.attributes.foo": [
      "The data.attributes.foo field is required."
    ]
  }
}
```

But with the JsonApiMiddleware, this response changes to:
```json
{
  "errors": [
    {
      "status": "422",
      "title": "Unprocessable Content",
      "detail": "The data.attributes.foo field is required.",
      "source": {
        "pointer": "data/attributes/foo",
        "parameter": "foo"
      }
    }
  ]
}
```

## Testing

There are some testing utilities on the `JsonApiResource` and `JsonApiResourceCollection` classes that you can utilize
to test your application.

Note that more testing utilities will be coming in future versions!

### assertHasData

```php
$author = Author::factory()->create();
$resource = JsonApiResource::make($author);

$id = $author->id;
$type = 'author';
$resource->assertHasData($id, $type);
```

### assertDoesntHaveData

```php
$author = Author::factory()->create();
$resource = JsonApiResourceCollection::make(collect([$author]));

$id = $author->id + 999;
$type = 'author';
$resource->assertDoesntHave($id, $type);
```

This is the opposite of `assertHasData`. It will fail if it finds the expected data.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Lars Bergvall](https://github.com/larsmbergvall)
- [All Contributors](../../contributors)
- [Spatie](https://spatie.be/) for the amazing Laravel package skeleton

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
