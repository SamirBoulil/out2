Feature: User registration
  In order to play the game
  As an unregistered user
  I should be able to register and show the first question

  Scenario: Register a new user and show the first question
    Given the following questions:
      | id                                   | statement                     | clue_1  | clue_2             | answer  |
      | 7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9 | Who is the greatest magician? | italian | it's like a hoodie | houdini |
    When I register:
      | argument    | value           |
      | name        | samir boulil    |
      | external-id | @my_external_id |
    Then I should see the text "You are successfully registered!"
    Then I should see the text "Here is your first question:"
    Then I should see the text "Who is the greatest magician?"

  Scenario: Shows an error when the external user id does not exist
    Given the following questions:
      | id                                   | statement                     | clue_1  | clue_2             | answer  |
      | 7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9 | Who is the greatest magician? | italian | it's like a hoodie | houdini |
    When I show my current question:
      | argument    | value                     |
      | external-id | @unknown_external_user_id |
    Then I should see the text "Sorry an error occured while trying to retrieve the question for user "@unknown_external_user_id"."
    And I should see the text "It seems the user with external id "@unknown_external_user_id" is not registered."
