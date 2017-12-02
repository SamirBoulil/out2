Feature: A user answer a question
  In order to advance in the leaderboard
  As a registered user of the game
  I need to be able to answer a question.

  Background:
    Given the user "Samir" is a registered user with id "3a021c08-ad15-43aa-aba3-8626fecd39a7"
    And the question with id "7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9" and statement "Who rode an Elephant ?" has "@<123456>" for answer

  Scenario: A user answers a question with the wrong answer
    When the user "Samir" answers the question "7d7fd0b2-0cb5-42ac-b697-3f7bfce24df9" with answer "<@wrong_answer>"
    Then there should be a question answered by the user "Samir"

