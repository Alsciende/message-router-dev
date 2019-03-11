Feature:
  Get a collection of posts

  Scenario: I can get the list
    When I request GET "/posts"
    Then the response code is 200
    When the response content is valid JSON
    Then the JSON node "success" should be true
    Then the JSON node "data" should exist
    And the JSON node "data" should have 1 element
    And the JSON node "data[0]" should be an object
    And the JSON node "data[0].id" should exist
