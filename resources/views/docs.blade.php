<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/5.24.0/swagger-ui-bundle.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/5.24.0/swagger-ui.min.css" rel="stylesheet">
</head>
<body>
    <div id="swagger-ui">

    </div>
    <script>
        const ui = SwaggerUIBundle({
            url: "{{ config('app.url') }}/docs/json",
            dom_id: '#swagger-ui'
        })
    </script>
</body>
</html>
