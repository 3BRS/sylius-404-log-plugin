@managing_404_logs
Feature: Managing 404 error logs
    In order to monitor and manage 404 errors on my website
    As an Administrator
    I want to be able to view, filter, and delete 404 error logs

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator
        And there are no 404 logs in the database

    @ui
    Scenario: Viewing all 404 logs
        Given there are 5 404 logs for "/non-existent-page" on domain "example.com"
        And there are 3 404 logs for "/another-page" on domain "example.com"
        When I browse 404 logs
        Then I should see 8 404 logs in the list
        And I should see a log for URL "/non-existent-page"
        And I should see a log for URL "/another-page"
        And I should see a log for domain "example.com"

    @ui
    Scenario: Viewing empty list of 404 logs
        Given there are no 404 logs in the database
        When I browse 404 logs
        Then I should see empty list of 404 logs

    @ui
    Scenario: Viewing aggregated 404 logs grouped by URL
        Given there are 10 404 logs for "/missing-product" on domain "shop.example.com"
        And there are 5 404 logs for "/old-category" on domain "shop.example.com"
        And there are 3 404 logs for "/test" on domain "test.example.com"
        When I browse aggregated 404 logs
        Then I should see 3 aggregated 404 logs in the list
        And I should see an aggregated log for "shop.example.com" "/missing-product"
        And I should see an aggregated log for "shop.example.com" "/old-category"
        And I should see an aggregated log for "test.example.com" "/test"
        And the aggregated log for "shop.example.com" "/missing-product" should show 10 occurrences
        And the aggregated log for "shop.example.com" "/old-category" should show 5 occurrences
        And the aggregated log for "test.example.com" "/test" should show 3 occurrences

    @ui
    Scenario: Viewing empty list of aggregated 404 logs
        Given there are no 404 logs in the database
        When I browse aggregated 404 logs
        Then I should see empty list of aggregated 404 logs

    @ui
    Scenario: Filtering aggregated logs by domain
        Given there are the following 404 logs:
            | domain           | url_slug         | count |
            | shop.example.com | /product-1       | 5     |
            | shop.example.com | /product-2       | 3     |
            | test.example.com | /test-page       | 2     |
            | blog.example.com | /old-post        | 4     |
        When I browse aggregated 404 logs
        And I filter aggregated logs by domain "shop.example.com"
        Then I should see 2 aggregated 404 logs in the list
        And I should see an aggregated log for "shop.example.com" "/product-1"
        And I should see an aggregated log for "shop.example.com" "/product-2"
        And I should not see an aggregated log for "test.example.com" "/test-page"
        And I should not see an aggregated log for "blog.example.com" "/old-post"

    @ui
    Scenario: Filtering aggregated logs by URL path
        Given there are the following 404 logs:
            | domain           | url_slug         | count |
            | example.com      | /products/old-1  | 5     |
            | example.com      | /products/old-2  | 3     |
            | example.com      | /categories/new  | 2     |
        When I browse aggregated 404 logs
        And I filter aggregated logs by URL path "/products"
        Then I should see 2 aggregated 404 logs in the list
        And I should see an aggregated log for "example.com" "/products/old-1"
        And I should see an aggregated log for "example.com" "/products/old-2"
        And I should not see an aggregated log for "example.com" "/categories/new"

    @ui
    Scenario: Filtering aggregated logs by minimum occurrence count
        Given there are the following 404 logs:
            | domain      | url_slug    | count |
            | example.com | /popular    | 50    |
            | example.com | /common     | 25    |
            | example.com | /rare       | 5     |
            | example.com | /very-rare  | 1     |
        When I browse aggregated 404 logs
        And I filter aggregated logs by minimum count 10
        Then I should see 2 aggregated 404 logs in the list
        And I should see an aggregated log for "example.com" "/popular"
        And I should see an aggregated log for "example.com" "/common"
        And I should not see an aggregated log for "example.com" "/rare"
        And I should not see an aggregated log for "example.com" "/very-rare"

    @ui
    Scenario: Filtering aggregated logs by maximum occurrence count
        Given there are the following 404 logs:
            | domain      | url_slug    | count |
            | example.com | /popular    | 50    |
            | example.com | /common     | 25    |
            | example.com | /rare       | 5     |
            | example.com | /very-rare  | 1     |
        When I browse aggregated 404 logs
        And I filter aggregated logs by maximum count 10
        Then I should see 2 aggregated 404 logs in the list
        And I should see an aggregated log for "example.com" "/rare"
        And I should see an aggregated log for "example.com" "/very-rare"
        And I should not see an aggregated log for "example.com" "/popular"
        And I should not see an aggregated log for "example.com" "/common"

    @ui
    Scenario: Viewing detailed statistics for a specific 404 URL
        Given there are 9 404 logs for "/broken-link" on domain "example.com"
        When I browse aggregated 404 logs
        And I view details for "example.com" "/broken-link"
        Then I should see 9 individual logs on the details page
        And I should see statistics for the 404 errors
        And I should see a chart with trend data

    @ui
    Scenario: Deleting all logs for a specific URL
        Given there are 10 404 logs for "/to-delete" on domain "example.com"
        And there are 5 404 logs for "/to-keep" on domain "example.com"
        When I browse aggregated 404 logs
        And I delete logs for "example.com" "/to-delete"
        Then I should be notified that the logs have been deleted
        And I should see 1 aggregated 404 logs in the list
        And I should not see an aggregated log for "example.com" "/to-delete"
        And I should see an aggregated log for "example.com" "/to-keep"
