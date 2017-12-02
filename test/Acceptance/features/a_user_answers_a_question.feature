Feature: A user answer a question
  In order to advance in the leaderboard
  As a registered user of the game
  I need to be able to answer a question.

  Background:
    Given the user "Samir" is a registered user with id "3a021c08-ad15-43aa-aba3-8626fecd39a7" and SlackId "<\@testUser>"
    And the question:
      | id                                   | statement              | answer           | clue1 | clue2  |
      | 7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9 | Who rode an Elephant ? | <\@right_answer> | clue1 | clue 2 |

  Scenario: A user incorrectly answers a question
    When the user "<\@testUser>" answers the question "7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9" with answer "<\@wrong_answer>"
    Then the answer should be incorrect

  Scenario: A user correctly answers a question
    When the user "<\@testUser>" answers the question "7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9" with answer "<\@right_answer>"
    Then the answer should be correct
