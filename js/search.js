$(document).ready(function () {
  // Cache the elements
  const $searchInput = $("#search");
  const $clearButton = $(".clear-search");
  const $showList = $("#show-list");

  // Send Search Text to the server
  $searchInput.on("keyup", function () {
      let searchText = $(this).val();
      if (searchText != "") {
          $.ajax({
              url: "search.php",
              method: "post",
              data: {
                  query: searchText,
              },
              success: function (response) {
                  $showList.html(response);
              },
          });
          $clearButton.css("display", "block"); // Show "X" when input has value
      } else {
          $showList.html("");
          $clearButton.css("display", "none"); // Hide "X" when input is empty
      }
  });

  // Clear the search input and results when "X" is clicked
  $clearButton.on("click", function () {
      $searchInput.val(""); // Clear the input
      $showList.html("");   // Clear the results
      $clearButton.css("display", "none"); // Hide the "X"
      $searchInput.focus(); // Optional: Refocus the input
  });

  // Set searched text in input field on click of search result
  $(document).on("click", "#show-list a", function () {
      $searchInput.val($(this).text());
      $showList.html("");
      $clearButton.css("display", "block"); // Show "X" since input now has value
  });
});
