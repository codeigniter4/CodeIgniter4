<?php
$page = <<<EOT
FORMAT: 1A
HOST: {$site_url}{$version}


# Group {$uc_plural}

{$uc_single}-related resources in the API.


## {$uc_plural} Collection [/{$plural}]


### List {$uc_plural} [GET]

Lists all {$plural} in a paginated manner.

+ Response 200 (application/json)

        {
            "total": 1234,
            "first": 1,
            "last": 20,
            "prev_url": null,
            "next_url": "{$site_url}/{$version}/{$plural}?page=2&per_page=20",
            "{$plural}": [
                {
                    {$formatted}
                    "meta": {
                        "url": "http://api.example.com/v1/{$plural}/1
                    }
                }
            ]
        }

+ Response 404 (application/json)

        {
            "error": "resource_not_found",
            "error_description": "Unable to find any {$plural} that match your request."
        }



### Create a new {$uc_single} [POST]

Allows you to create a new {$uc_single}. The information should be submitted as a standard form submittal.

+ email (string) - The user's email address
+ username (string) - The user's desired username
+ password (string) - The user's desired password
+ pass_confirm (string) - Verification of the password


+ Response 201 (application/json)

        {
            {$formatted}
            "meta": {
                "url": "http://api.example.com/v1/{$plural}/1
            }
        }

+ Response 409 (application/json)

        {
            "error": "resource_exists",
            "error_description": "A {$uc_single} with that email already exists."
        }

+ Response 400 (application/json)

        {
            "error": "invalid_request",
            "error_description": "<p>The username is already in use on this site.</p>"
        }

+ Response 500 (application/json)

        {
            "error": "server_error",
            "error_description": "Unknown error creating user."
        }



## {$uc_single} [/{$plural}/{{$single}_id}]

{$uc_plural} can have the following attributes:

- email
- username
- password
- pass_confirm
- status
- status_message
- active
- deleted

+ Parameters
    + {$single}_id (required, number, `1`) ... An integer that is the ID of the user

### Get a Single {$uc_single} [GET]

+ Response 200 (application/json)

        {
            {$formatted}
            "meta": {
            "url": "http://api.example.com/v1/{$plural}/1
            }
        }

+ Response 404 (application/json)

        {
            "error": "resource_not_found",
            "error_description": "Unable to find that user."
        }

+ Response 410 (application/json)

        {
            "error": "resource_gone",
            "error_description": "That user has been deleted."
        }



### Update a {$uc_single} [PUT]

+ Response 200 (application/json)

        {
            {$formatted}
            "meta": {
                "url": "http://api.example.com/v1/{$plural}/1
            }
        }

+ Response 404 (application/json)

        {
            "error": "resource_not_found",
            "error_description": "Unable to find that {$uc_single}."
        }

+ Response 400 (application/json)

        {
            "error": "bad_request",
            "error_description": "No data found to update."
        }

+ Response 400 (application/json)

        {
            "error": "bad_request",
            "error_description": "<p>The username is already in use on this site.</p>"
        }

+ Response 500 (application/json)

        {
            "error": "server_error",
            "error_description": "Unknown error saving user."
        }



### Delete A {$uc_single} [DELETE]

+ Response 200 (application/json)

        {
            "response": "{$uc_single} was deleted"
        }

+ Response 404 (application/json)

        {
            "error": "resource_not_found",
            "error_description": "Unable to find that {$uc_single}."
        }

+ Response 500 (application/json)

        {
            "error": "server_error",
            "error_description": "Unknown database error."
        }


## Creation Form [/{$plural}/new]

### Get Form [GET]

Returns the form needed to create a new {$uc_single}.

+ Response 200 (text/html)

        <form action="">
            ...
        </form>



## Editing Form [/{$plural}/{{$single}_id}/edit]

### Get Form [GET]

Returns the form needed to create a new {$uc_single}.

+ Response 200 (text/html)

        <form action="">
            ...
        </form>

+ Response 404 (application/json)

        {
            "error": "resource_not_found",
            "error_description": "{$uc_single} was not found."
        }

+ Response 410 (application/json)

        {
            "error": "resource_gone",
            "error_description": "That {$uc_single} has been deleted."
        }

EOT;

echo $page;