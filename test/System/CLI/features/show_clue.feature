Feature: Show the clue
  In order to play the game
  As a registered user
  I should be able to show the current clue the game gave me

  Background:
    Given the following questions:
      | id                                   | statement                     | clue_1  | clue_2             | answer  |
      | AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA | Who is the greatest magician? | italian | it's like a hoodie | houdini |
    And the following users:
      | id                                   | external_user_id | name  |
      | 22222222-2222-2222-2222-222222222222 | @my_external_id  | Samir |

  Scenario: It does not show the clue if the user hasn't received any
    Given the following events:
      | type             | user_id                              | question_id                          | is_correct |
      | questionAsked    | 22222222-2222-2222-2222-222222222222 | AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA |            |
      | questionAnswered | 22222222-2222-2222-2222-222222222222 | AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA | false      |
    When I run the command "out:show-clue" with the following arguments:
      | argument    | value           |
      | external-id | @my_external_id |
    Then I should see the text "Clue: italian"

  Scenario: It does not show the clue if the user hasn't received any
    Given the following events:
      | type          | user_id                              | question_id                          |
      | questionAsked | 22222222-2222-2222-2222-222222222222 | AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA |
    When I run the command "out:show-clue" with the following arguments:
      | argument    | value           |
      | external-id | @my_external_id |
    Then I should see the text "There is no clue to show."

  Scenario: It does not show the clue if the user has completed the quiz
    Given the following events:
      | type             | user_id                              | question_id                          | is_correct |
      | questionAsked    | 22222222-2222-2222-2222-222222222222 | AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA |            |
      | questionAnswered | 22222222-2222-2222-2222-222222222222 | AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA | true       |
      | quizCompleted    | 22222222-2222-2222-2222-222222222222 |                                      |            |
    When I run the command "out:show-clue" with the following arguments:
      | argument    | value           |
      | external-id | @my_external_id |
    Then I should see the text "You've completed the quiz! there is no clue to show."

  Scenario: Shows an error when the external user id does not exist
    When I run the command "out:show-clue" with the following arguments:
      | argument    | value                     |
      | external-id | @unknown_external_user_id |
    Then I should see the text "Sorry an error occured while trying to retrieve the clue for the question "@unknown_external_user_id"."
    And I should see the text "It seems the user with external id "@unknown_external_user_id" is not registered."
