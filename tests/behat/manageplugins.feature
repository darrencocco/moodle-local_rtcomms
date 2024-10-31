@local @local_rtcomms
Feature: Management of real time backend plugins
  In order to configure real time events
  As an admin
  I need to be able to manage real time backend plugins

  Scenario: View the real time communications plugin page
    Given I log in as "admin"
    When I navigate to "Plugins > Admin tools > Real time events" in site administration
    Then I should see "Available real time comms backend plugins"
    And I should see "PHP polling"
    And I log out
