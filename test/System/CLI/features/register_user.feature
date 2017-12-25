Feature: User registration
  In order to play the game
  As an unregistered user
  I should be able to register and show the first question

  Scenario: Register a new user
    Given the following questions:
      | id                                   | statement                      | clue_1    | clue_2               | answer  |
      | 7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9 | "Who is the greatest magician" | "italian" | "it's like a hoodie" | houdini |
    When I run the command "out:register" with the following arguments:
      | argument    | value           |
      | name        | samir boulil    |
      | external-id | @my_external_id |
    Then I should see the text "<success>You are successfully registered</success>"
