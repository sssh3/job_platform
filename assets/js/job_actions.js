$(".status-action").click(function() {
    var button = $(this);
    var applicationId = button.data("application-id");
    var action = button.data("action");

    // Check if applicationId and action are valid
    console.log("Application ID:", applicationId, "Action:", action);

    // Disable button to prevent multiple clicks
    button.prop("disabled", true).text("Processing...");

    $.ajax({
        url: "",  // Current page
        method: "POST",
        data: {
            action: action,
            application_id: applicationId
        },
        success: function(response) {
            var data = JSON.parse(response);
            if (data.status === "success") {
                // Update status text and hide button
                $("#status-" + applicationId).text(data.action.charAt(0).toUpperCase() + data.action.slice(1));
                button.hide();  // Hide the button after success
                alert("Action completed successfully!");
            } else {
                alert("Error: " + data.message);
                button.prop("disabled", false).text(action.charAt(0).toUpperCase() + action.slice(1));
            }
        },
        error: function() {
            alert("An error occurred. Please try again.");
            button.prop("disabled", false).text(action.charAt(0).toUpperCase() + action.slice(1));
        }
    });
});
