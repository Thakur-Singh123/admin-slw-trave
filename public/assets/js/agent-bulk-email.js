/* =========================================================
   SLW SUPER ADMIN - SEND BULK EMAIL PAGE
========================================================= */

document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    const bulkEmailForm = document.getElementById("bulkEmailForm");
    const agentSelect = document.getElementById("agentSelect");
    const agentSelectButton = document.getElementById("agentSelectButton");
    const agentDropdown = document.getElementById("agentDropdown");
    const agentSearch = document.getElementById("agentSearch");
    const statusFilter = document.getElementById("agentStatusFilter");
    const countryFilter = document.getElementById("agentCountryFilter");
    const selectAllAgents = document.getElementById("selectAllAgents");
    const onlyActiveAgents = document.getElementById("onlyActiveAgents");
    const clearAgentSelection = document.getElementById("clearAgentSelection");
    const applyAgentSelection = document.getElementById("applyAgentSelection");
    const agentCheckboxes = Array.from(
        document.querySelectorAll(".agent-checkbox")
    );
    const agentOptions = Array.from(document.querySelectorAll(".agent-option"));

    const selectedAgentCount = document.getElementById("selectedAgentCount");
    const selectedAgentStat = document.getElementById("selectedAgentStat");
    const agentSelectLabel = document.getElementById("agentSelectLabel");
    const visibleAgentCount = document.getElementById("visibleAgentCount");

    const emailTemplate = document.getElementById("emailTemplate");
    const emailSubject = document.getElementById("emailSubject");
    const emailBody = document.getElementById("emailBody");
    const subjectCount = document.getElementById("subjectCount");
    const bodyCount = document.getElementById("bodyCount");
    const emailAttachment = document.getElementById("emailAttachment");
    const attachmentName = document.getElementById("attachmentName");

    const saveDraftButton = document.getElementById("saveDraftButton");
    const sendTestEmailButton = document.getElementById("sendTestEmailButton");
    const testEmailAddress = document.getElementById("testEmailAddress");

    const templateData = {
        custom: {
            subject: "",
            body: ""
        },
        announcement: {
            subject: "Important update from Sun Leisure World",
            body:
                "Dear {agent_name},\n\nWe have an important update regarding the SLW B2B Agent Portal. Please log in to your account to view the latest information.\n\nIf you need technical assistance, please contact our support team.\n\nRegards,\nSun Leisure World Team"
        },
        promotion: {
            subject: "Exclusive B2B offer for SLW travel agents",
            body:
                "Dear {agent_name},\n\nA new special offer is now available for {company_name}. Log in to the SLW B2B Portal to check the latest rates and availability.\n\nBook early to secure the best available price.\n\nRegards,\nSun Leisure World Team"
        },
        maintenance: {
            subject: "Scheduled maintenance notice - SLW B2B Portal",
            body:
                "Dear {agent_name},\n\nThe SLW B2B Portal will undergo scheduled maintenance. Some services may be temporarily unavailable during this period.\n\nWe apologize for the inconvenience and appreciate your understanding.\n\nRegards,\nSLW Technical Team"
        },
        training: {
            subject: "Invitation: SLW B2B Agent Portal training",
            body:
                "Dear {agent_name},\n\nYou are invited to attend an online training session for the SLW B2B Agent Portal. We will explain bookings, wallet top-ups, vouchers and other portal features.\n\nTraining date and meeting details will be shared shortly.\n\nRegards,\nSun Leisure World Team"
        }
    };

    function openAgentDropdown() {
        agentSelect.classList.add("open");
        agentSelectButton.setAttribute("aria-expanded", "true");
        window.setTimeout(function () {
            agentSearch.focus();
        }, 100);
    }

    function closeAgentDropdown() {
        agentSelect.classList.remove("open");
        agentSelectButton.setAttribute("aria-expanded", "false");
    }

    function toggleAgentDropdown() {
        if (agentSelect.classList.contains("open")) {
            closeAgentDropdown();
        } else {
            openAgentDropdown();
        }
    }

    function getVisibleOptions() {
        return agentOptions.filter(function (option) {
            return !option.classList.contains("hidden");
        });
    }

    function filterAgents() {
        const searchValue = agentSearch.value.trim().toLowerCase();
        const statusValue = statusFilter.value;
        const countryValue = countryFilter.value;

        agentOptions.forEach(function (option) {
            const matchesSearch = option.dataset.search.includes(searchValue);
            const matchesStatus =
                statusValue === "all" || option.dataset.status === statusValue;
            const matchesCountry =
                countryValue === "all" || option.dataset.country === countryValue;

            option.classList.toggle(
                "hidden",
                !(matchesSearch && matchesStatus && matchesCountry)
            );
        });

        const visibleOptions = getVisibleOptions();
        visibleAgentCount.textContent =
            visibleOptions.length +
            (visibleOptions.length === 1 ? " agent available" : " agents available");

        updateSelectAllState();
    }

    function updateSelectAllState() {
        const visibleCheckboxes = getVisibleOptions().map(function (option) {
            return option.querySelector(".agent-checkbox");
        });

        const checkedVisible = visibleCheckboxes.filter(function (checkbox) {
            return checkbox.checked;
        }).length;

        selectAllAgents.checked =
            visibleCheckboxes.length > 0 &&
            checkedVisible === visibleCheckboxes.length;

        selectAllAgents.indeterminate =
            checkedVisible > 0 && checkedVisible < visibleCheckboxes.length;
    }

    function updateSelectedAgentCount() {
        const totalSelected = agentCheckboxes.filter(function (checkbox) {
            return checkbox.checked;
        }).length;

        selectedAgentCount.textContent = totalSelected;
        selectedAgentStat.textContent = totalSelected;

        if (totalSelected === 0) {
            agentSelectLabel.textContent = "Choose agents";
        } else if (totalSelected === 1) {
            agentSelectLabel.textContent = "1 agent selected";
        } else {
            agentSelectLabel.textContent = totalSelected + " agents selected";
        }

        updateSelectAllState();
    }

    function setOnlyActiveAgents() {
        if (!onlyActiveAgents.checked) {
            return;
        }

        agentOptions.forEach(function (option) {
            const checkbox = option.querySelector(".agent-checkbox");

            if (option.dataset.status !== "active") {
                checkbox.checked = false;
            }
        });

        statusFilter.value = "active";
        filterAgents();
        updateSelectedAgentCount();
    }

    function updateCharacterCounters() {
        subjectCount.textContent = emailSubject.value.length;
        bodyCount.textContent = emailBody.value.length;
    }

    function insertTextAroundSelection(beforeText, afterText) {
        const selectionStart = emailBody.selectionStart;
        const selectionEnd = emailBody.selectionEnd;
        const selectedText = emailBody.value.substring(selectionStart, selectionEnd);
        const replacement = beforeText + selectedText + afterText;

        emailBody.setRangeText(
            replacement,
            selectionStart,
            selectionEnd,
            "select"
        );

        emailBody.focus();
        updateCharacterCounters();
    }

    function showPageMessage(message, type) {
        const oldMessage = document.querySelector(".email-page-alert");

        if (oldMessage) {
            oldMessage.remove();
        }

        const alertBox = document.createElement("div");
        alertBox.className =
            "alert alert-" +
            (type === "success" ? "success" : "danger") +
            " email-page-alert";
        alertBox.setAttribute("role", "alert");
        alertBox.innerHTML =
            '<i class="bi bi-' +
            (type === "success" ? "check-circle" : "exclamation-circle") +
            '"></i> ' +
            message;

        const pageHeading = document.querySelector(".email-page-heading");
        pageHeading.insertAdjacentElement("afterend", alertBox);

        window.setTimeout(function () {
            alertBox.remove();
        }, 4500);
    }

    agentSelectButton.addEventListener("click", function (event) {
        event.stopPropagation();
        toggleAgentDropdown();
    });

    agentDropdown.addEventListener("click", function (event) {
        event.stopPropagation();
    });

    document.addEventListener("click", function (event) {
        if (!agentSelect.contains(event.target)) {
            closeAgentDropdown();
        }
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeAgentDropdown();
        }
    });

    agentSearch.addEventListener("input", filterAgents);
    statusFilter.addEventListener("change", filterAgents);
    countryFilter.addEventListener("change", filterAgents);

    agentCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener("change", function () {
            if (
                onlyActiveAgents.checked &&
                checkbox.closest(".agent-option").dataset.status !== "active"
            ) {
                checkbox.checked = false;
                showPageMessage(
                    "Active agents only is enabled. Please disable it to select this agent.",
                    "error"
                );
            }

            updateSelectedAgentCount();
        });
    });

    selectAllAgents.addEventListener("change", function () {
        getVisibleOptions().forEach(function (option) {
            const checkbox = option.querySelector(".agent-checkbox");
            const canSelect =
                !onlyActiveAgents.checked || option.dataset.status === "active";

            checkbox.checked = selectAllAgents.checked && canSelect;
        });

        updateSelectedAgentCount();
    });

    onlyActiveAgents.addEventListener("change", function () {
        if (onlyActiveAgents.checked) {
            setOnlyActiveAgents();
        } else {
            statusFilter.value = "all";
            filterAgents();
        }
    });

    clearAgentSelection.addEventListener("click", function () {
        agentCheckboxes.forEach(function (checkbox) {
            checkbox.checked = false;
        });

        selectAllAgents.checked = false;
        selectAllAgents.indeterminate = false;
        updateSelectedAgentCount();
    });

    applyAgentSelection.addEventListener("click", function () {
        updateSelectedAgentCount();
        closeAgentDropdown();
    });

    emailTemplate.addEventListener("change", function () {
        const selectedTemplate = templateData[emailTemplate.value];

        if (!selectedTemplate) {
            return;
        }

        if (
            (emailSubject.value.trim() !== "" || emailBody.value.trim() !== "") &&
            emailTemplate.value !== "custom"
        ) {
            const replaceContent = window.confirm(
                "Current subject and email body will be replaced. Continue?"
            );

            if (!replaceContent) {
                emailTemplate.value = "custom";
                return;
            }
        }

        emailSubject.value = selectedTemplate.subject;
        emailBody.value = selectedTemplate.body;
        updateCharacterCounters();
    });

    emailSubject.addEventListener("input", updateCharacterCounters);
    emailBody.addEventListener("input", updateCharacterCounters);

    document.querySelectorAll(".editor-toolbar button").forEach(function (button) {
        button.addEventListener("click", function () {
            const command = button.dataset.command;

            if (command === "bold") {
                insertTextAroundSelection("**", "**");
            } else if (command === "italic") {
                insertTextAroundSelection("*", "*");
            } else if (command === "underline") {
                insertTextAroundSelection("<u>", "</u>");
            } else if (command === "insertUnorderedList") {
                insertTextAroundSelection("\nâ€¢ ", "");
            } else if (command === "insertOrderedList") {
                insertTextAroundSelection("\n1. ", "");
            } else {
                const linkUrl = window.prompt("Enter the complete link URL:", "https://");

                if (linkUrl && linkUrl !== "https://") {
                    insertTextAroundSelection("", " (" + linkUrl + ")");
                }
            }
        });
    });

    emailAttachment.addEventListener("change", function () {
        const selectedFile = emailAttachment.files[0];

        if (!selectedFile) {
            attachmentName.textContent = "Choose a file";
            return;
        }

        const maximumFileSize = 10 * 1024 * 1024;

        if (selectedFile.size > maximumFileSize) {
            emailAttachment.value = "";
            attachmentName.textContent = "Choose a file";
            showPageMessage("Attachment must be smaller than 10 MB.", "error");
            return;
        }

        attachmentName.textContent = selectedFile.name;
    });

    saveDraftButton.addEventListener("click", function () {
        const draftData = {
            senderName: document.getElementById("senderName").value,
            replyTo: document.getElementById("replyTo").value,
            template: emailTemplate.value,
            subject: emailSubject.value,
            body: emailBody.value,
            selectedAgents: agentCheckboxes
                .filter(function (checkbox) {
                    return checkbox.checked;
                })
                .map(function (checkbox) {
                    return checkbox.value;
                })
        };

        localStorage.setItem("slwAgentEmailDraft", JSON.stringify(draftData));
        showPageMessage("Email draft saved in this browser.", "success");
    });

    sendTestEmailButton.addEventListener("click", function () {
        const testEmail = testEmailAddress.value.trim();

        if (!testEmailAddress.checkValidity() || testEmail === "") {
            testEmailAddress.classList.add("is-invalid");
            return;
        }

        testEmailAddress.classList.remove("is-invalid");

        if (emailSubject.value.trim() === "" || emailBody.value.trim() === "") {
            showPageMessage(
                "Please enter the email subject and body before sending a test.",
                "error"
            );
            return;
        }

        const modalElement = document.getElementById("testEmailModal");
        const modalInstance = bootstrap.Modal.getInstance(modalElement);

        if (modalInstance) {
            modalInstance.hide();
        }

        showPageMessage(
            "Test email is ready for " +
                testEmail +
                ". Connect this action with your PHP mail API.",
            "success"
        );
    });

    bulkEmailForm.addEventListener("submit", function (event) {
        event.preventDefault();

        const totalSelected = agentCheckboxes.filter(function (checkbox) {
            return checkbox.checked;
        }).length;

        if (!bulkEmailForm.checkValidity()) {
            bulkEmailForm.classList.add("was-validated");
            showPageMessage("Please complete all required email fields.", "error");
            return;
        }

        if (totalSelected === 0) {
            openAgentDropdown();
            showPageMessage("Please select at least one agent.", "error");
            return;
        }

        const confirmSend = window.confirm(
            "Review complete: send this email to " +
                totalSelected +
                (totalSelected === 1 ? " agent?" : " agents?")
        );

        if (!confirmSend) {
            return;
        }

        showPageMessage(
            "Email data is ready. Connect the form action with your PHP email-sending file.",
            "success"
        );
    });

    filterAgents();
    updateSelectedAgentCount();
    updateCharacterCounters();
});