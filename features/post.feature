Feature:
  Create a post

  Scenario: I can create a simple post
    Given the request body is:
      """
      {
        "title": "Lorem Ipsum"
      }
      """
    When I request POST "/posts"
    Then the response code is 200
    When the response content is valid JSON
    Then the JSON node "success" should be true
    And the JSON node "id" should exist

  Scenario: The request is validated
    Given the request body is:
      """
      {
        "title": ""
      }
      """
    When I request POST "/posts"
    Then the response code is 200
    When the response content is valid JSON
    Then the JSON node "success" should be false
    And the JSON node "error" should be an object
    And the JSON node "error.violations" should have 1 element
    And the JSON node "error.violations[0].propertyPath" should be the string "title"
    And the JSON node "error.violations[0].title" should be the string "This value should not be blank."
