Feature: Display the leaderboard
  In order to know all the users rank
  As an unregistered user
  I should be able to see the leaderboard

  Scenario: It displays the leaderboard
    Given the following questions:
      | id                                   | statement                      | clue_1     | clue_2                  | answer  |
      | AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA | Who is the greatest magician?  | italian    | it's like a hoodie      | houdini |
      | BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB | Who is the strongest athelete? | Jamaica    | It's an arrow           | bolt    |
      | CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC | Who would do judo ?            | He is bold | Only with a beer or two | jm      |
    And the following users:
      | id                                   | external_user_id | name   |
      | 11111111-1111-1111-1111-111111111111 | @samir           | Samir  |
      | 22222222-2222-2222-2222-222222222222 | @julien          | Julien |
      | 33333333-3333-3333-3333-333333333333 | @jm              | JM     |
    And the following events:
      | type             | user_id                              | question_id                          | is_correct |
      # samir hasn't answered correctly yet.
      | questionAsked    | 11111111-1111-1111-1111-111111111111 | AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA |            |
      | questionAnswered | 11111111-1111-1111-1111-111111111111 | AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA | false      |
      | questionAnswered | 11111111-1111-1111-1111-111111111111 | AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA | false      |
      | questionAnswered | 11111111-1111-1111-1111-111111111111 | AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA | false      |
      | questionAsked    | 11111111-1111-1111-1111-111111111111 | BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB |            |
      | questionAnswered | 11111111-1111-1111-1111-111111111111 | BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB | false      |
      | questionAnswered | 11111111-1111-1111-1111-111111111111 | BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB | false      |
      | questionAnswered | 11111111-1111-1111-1111-111111111111 | BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB | false      |
      | questionAsked    | 11111111-1111-1111-1111-111111111111 | CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC |            |
      | questionAnswered | 11111111-1111-1111-1111-111111111111 | CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC | false      |
      | questionAnswered | 11111111-1111-1111-1111-111111111111 | CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC | false      |
      | questionAnswered | 11111111-1111-1111-1111-111111111111 | CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC | false      |
      | questionAsked    | 11111111-1111-1111-1111-111111111111 | AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA |            |
      # julien answered correctly on the first try for all questions
      | questionAsked    | 22222222-2222-2222-2222-222222222222 | AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA |            |
      | questionAnswered | 22222222-2222-2222-2222-222222222222 | AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA | true       |
      | questionAsked    | 22222222-2222-2222-2222-222222222222 | BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB |            |
      | questionAnswered | 22222222-2222-2222-2222-222222222222 | BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB | true       |
      | questionAsked    | 22222222-2222-2222-2222-222222222222 | CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC |            |
      | questionAnswered | 22222222-2222-2222-2222-222222222222 | CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC | true       |
      | quizCompleted    | 22222222-2222-2222-2222-222222222222 |                                      |            |
      # JM answered correctly partially
      | questionAsked    | 33333333-3333-3333-3333-333333333333 | AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA |            |
      | questionAnswered | 33333333-3333-3333-3333-333333333333 | AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA | false      |
      | questionAnswered | 33333333-3333-3333-3333-333333333333 | AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA | true       |
      | questionAsked    | 33333333-3333-3333-3333-333333333333 | BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB |            |
      | questionAnswered | 33333333-3333-3333-3333-333333333333 | BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB | false      |
      | questionAnswered | 33333333-3333-3333-3333-333333333333 | BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB | false      |
      | questionAnswered | 33333333-3333-3333-3333-333333333333 | BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB | false      |
      | questionAsked    | 33333333-3333-3333-3333-333333333333 | CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC |            |
      | questionAnswered | 33333333-3333-3333-3333-333333333333 | CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC | false      |
      | questionAnswered | 33333333-3333-3333-3333-333333333333 | CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC | false      |
      | questionAnswered | 33333333-3333-3333-3333-333333333333 | CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC | true       |
      | questionAsked    | 33333333-3333-3333-3333-333333333333 | BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB |            |
    When I run the command "out:leaderboard":
    Then I should see following table:
    """
    +------+--------+--------+
    | Rank | Name   | Points |
    +------+--------+--------+
    | 1    | Julien | 9      |
    | 2    | JM     | 3      |
    | 3    | Samir  | 0      |
    +------+--------+--------+
    """
