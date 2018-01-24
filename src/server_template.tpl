<?php

{% if code %}
  http_response_code({{ code }});
{% endif %}

{% for header in headers %}
  header("{{ header }}");
{% endfor %}

print "{{ body }}";
