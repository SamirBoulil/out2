Feature: A user answer a question
  In order to not brake the quiz
  As a non registered user
  I cannot be able to answer a question

  Background:
    Given the question:
      | id                                   | statement              | answer           | clue1  | clue2  |
      | 7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9 | Who rode an Elephant ? | <\@right_answer> | clue 1 | clue 2 |

  Scenario: A unknown user answers a question
    When the user "00000000-0000-0000-0000-000000000000" answers "<\@right_answer>"
    Then the user id should not be known
    And there should be no question answered
