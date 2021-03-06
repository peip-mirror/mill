---
id: api-param
title: "@api-param"
---

A request parameter that can be supplied to a resource action via a body payload.

> If you need to describe a parameter that can be used within a query string, use the [`@api-queryparam`](reference-api-queryparam.md) annotation.

## Syntax
```php
@api-param:visibility fieldName `sampleData` (type, required|optional, nullable, vendor:tagName) - Description
    + Members
        - `option` - Option description
```

## Requirements
| Required? | Needs a visibility | Supports versioning | Supports deprecation |
| :--- | :--- | :--- | :--- |
| × | ✓ | ✓ | ✓ |

## Breakdown
| Tag | Optional | Description |
| :--- | :--- | :--- |
| :visibility | ✓ | [Visibility decorator](reference-visibility.md) |
| fieldName | × | This is the name of the variable that the developer should pass in the request. |
| sampleData | ✓ | This is a sample of what the contents of the parameter should be. For example, if you're passing in a number, this can be "50". |
| type | × | This can be a reference to the type of variable that is being passed in (string, boolean, array, etc.), or can be one of the [tokens](#tokens) that are configured for your API. |
| required&vert;optional | ✓ | A flag that indicates that the parameter is, well, optional. If nothing is supplied, it defaults to being `optional`. |
| nullable | ✓ | A flag that indicates that the parameter is nullable. If nothing is supplied, it defaults to being non-nullable. |
| vendor:tagName | ✓ | Defined vendor tag. See the [`@api-vendortag`](reference-api-vendortag.md) documentation for more information. There is no limit to the amount of vendor tags you can specify on a parameter. |
| Description | × | Description for what the parameter is for. |
| Members | ✓ | If this parameter has acceptable values (like in the case of an `enum` type), you can document those values here along with a description for what the value is, or means. |

### Supported Types
| Type | Specification representation |
| :--- | :--- |
| array | array |
| boolean | boolean |
| date | string |
| datetime | string |
| float | number |
| enum | enum |
| integer | number |
| number | number |
| object | object |
| string | string |
| timestamp | string |
| uri | string |

#### Subtypes
Mill allows you, if necessary, to define a single subtype for a parameter. For example, if you have a parameter that is an array of objects, you can set the `type` as `array<object>`.

Currently only `array` types are allowed to contain subtypes. To define subtypes of objects, use an `@api-param` annotation for each child parameter.

## Tokens
Because writing out the same parameter for a large number of endpoints can get tiring, we have a system in place that allows you to configure tokens, which act as kind of a short-code for a parameter:

In your [configuration](configuration.md) file:

```xml
<parameterTokens>
    <token name="page">page (integer, optional) - The page number to show.</token>
    <token name="per_page">per_page (integer, optional) - Number of items to show on each page. Max 100.</token>
    <token name="filter">filter (string, optional) - Filter to apply to the results.</token>
</parameterTokens>
```

And then you can just reference the token as part of [`@api-param`](reference-api-param.md):

```php
@api-param:public {page}
```

You can also pass in any enum values into tokens just as you would with a regular parameter:

```php
@api-param:public {filter}
    + Members
        `embeddable`
        `playable`
```

## Examples
Using a token:

```php
@api-param:public {page}
```

Using a token with available values:

```php
@api-param:public {filter}
    + Members
        `embeddable`
        `playable`
```

With a vendor tag:

```php
@api-param:public locked_down (string, needs:SomeApplicationFeature) - This is a cool thing.
```

Normal usage with acceptable values:

```php
@api-param:private __testing (string) - This does a thing.
    + Members
        - `true`
        - `false`
```
