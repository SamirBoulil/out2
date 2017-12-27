Feature: Answer the question
  In order to play the game
  As a registered user
  I should be able to answer a question

  Background:
    Given the following questions:
      | id                                   | statement                      | clue_1  | clue_2             | answer  |
      | AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA | Who is the greatest magician?  | italian | it's like a hoodie | houdini |
      | BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB | Who is the strongest athelete? | Jamaica | It's an arrow      | bolt    |
    And the following users:
      | id                                   | external_user_id | name  |
      | 22222222-2222-2222-2222-222222222222 | @my_external_id  | Samir |
    And the following events:
      | type          | user_id                              | question_id                          |
      | questionAsked | 22222222-2222-2222-2222-222222222222 | AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA |

  Scenario: User answer correctly the question
    When I run the command "out:answer-question" with the following arguments:
      | argument    | value           |
      | external-id | @my_external_id |
      | answer      | houdini         |
    Then I should see the text "Correct! Well done!"
    Then I should see the text "Here is a new question for you:"
    And I should see the text "Who is the strongest athelete?"

#  Scenario: Answering incorrectly the question will show the first clue
#    When I run the command "out:answer-question" with the following arguments:
#      | argument    | value           |
#      | external-id | @my_external_id |
#    Then I should see the text "Clue 1: italian"
#
#  Scenario: Shows an error when the external user id does not exist
#    When I run the command "out:answer-question" with the following arguments:
#      | argument    | value                     |
#      | external-id | @unknown_external_user_id |
#    Then I should see the text "Sorry an error occured while trying to retrieve the question for user "@unknown_external_user_id"."
#    And I should see the text "It seems the user with external id "@unknown_external_user_id" is not registered."
