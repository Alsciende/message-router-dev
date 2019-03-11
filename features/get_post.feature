Feature:
  Get a post

  Scenario: I can get a post
    When I request GET "/posts/hello-world"
    Then the response code is 200
    When the response content is valid JSON
    Then the JSON node "success" should be true
    Then the JSON node "data" should exist
    And the JSON node "data.id" should be the string "hello-world"
