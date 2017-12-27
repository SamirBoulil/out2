Feature: Show the question
  In order to play the game
  As a registered user
  I should be able to show the question I have to answer

  Background:
    Given the following questions:
      | id                                   | statement                     | clue_1  | clue_2             | answer  |
      | AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA | Who is the greatest magician? | italian | it's like a hoodie | houdini |
    And the following users:
      | id                                   | external_user_id | name  |
      | 22222222-2222-2222-2222-222222222222 | @my_external_id  | Samir |
    And the following events:
      | type          | user_id                              | question_id                          |
      | questionAsked | 22222222-2222-2222-2222-222222222222 | AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA |

  Scenario: Show the question the user has to answer
    When I run the command "out:show-question" with the following arguments:
      | argument    | value           |
      | external-id | @my_external_id |
    Then I should see the text "Who is the greatest magician?"

  Scenario: Shows an error when the external user id does not exist
    When I run the command "out:show-question" with the following arguments:
      | argument    | value                     |
      | external-id | @unknown_external_user_id |
    Then I should see the text "Sorry an error occured while trying to retrieve the question for user "@unknown_external_user_id"."
    And I should see the text "It seems the user with external id "@unknown_external_user_id" is not registered."
