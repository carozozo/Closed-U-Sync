$(document).ready(function() {
    ContactInGroup.setDefault();
    ContactInGroup.setDefaultHandler();

    ContactRelationButton.setHandler();
});

var ContactInGroup = {
    defaultBgColor : '#B6CADE',
    activeBgColor : '#FFFFFF',
    selectedBgColor : '#698099',
    titleColor : '#193756',
    setDefault : function() {
        $('#contactInGroupTab').css('background-color', ContactInGroup.defaultBgColor).css('color', ContactInGroup.titleColor);
        $("#contactInGroupMainTable tr").css('background-color', ContactInGroup.defaultBgColor);
        $("#contactInGroupMainTable .titleTr").css('color', ContactInGroup.titleColor);
        $("#contactInGroupTabDiv").hide();
        $("#contactInGroupMainTable").hide();
        // $("#contactInGroupTitle").html("");
        $("#selectAllContactInGroupItem").hide();
        $("#selectAllContactInGroup").attr("checked", false);
        $(".contactRelationButton").hide();
        $(".contactRelationButton2").hide();
    },
    getContactListByGroupId : function() {
        //如果使用者有選取群組
        if (ContactGroup.selectedGroupId) {
            var groupId = ContactGroup.selectedGroupId;
            ContactInGroup.setDefault();
            // $("#contactInGroupTitle").html(t('contact', 'Contact in group') + ":");
            $("#contactInGroupTabDiv").show();
            $("#contactInGroupMainTable").show();
            $("#contactInGroupLoaginImg").show();
            $("#contactInGroupTable").hide();
            $.post(OC.filePath("contact", "ajax", "contact_group.php"), {
                action : "getContactListByGroupId",
                groupId : groupId,
            }, function(data) {
                if (data.status == "success") {
                    //除了樣版之外其它的tr都刪除
                    $("#selectAllContactInGroupItem").show();
                    $("#contactInGroupTable tr:gt(0)").remove();
                    //var resultArray = Contact.sortNickname(data.result);
                    var resultArray = data.result;
                    $.each(resultArray, function(index, val) {
                        var contact = val['contact'];
                        var nickname = val['nickname'];
                        if (contact.trim() != "") {
                            var cloneTr = $("#contactInGroupTable tr:first").clone();
                            cloneTr.find("#contactInGroup").val(contact);
                            cloneTr.find("#nicknameInGroup").html(nickname);
                            cloneTr.appendTo("#contactInGroupTable");
                            ContactInGroup.setHandler(cloneTr);
                        }
                        //隱藏樣版
                        $("#contactInGroupTable tr:first").hide();
                        $("#contactInGroupTable tr:gt(0)").show();
                    });
                    $("#contactInGroupTable").show();
                    $("#contactInGroupLoaginImg").hide();
                    $(".contactRelationButton").show();
                    $(".contactRelationButton2").hide();
                }
            });
        }
    },
    getContactListBySystemGroupId : function() {
        //如果使用者有選取群組
        if (ContactSystemGroup.selectedSystemGroupId) {
            var systemGroupId = ContactSystemGroup.selectedSystemGroupId;
            ContactInGroup.setDefault();
            // $("#contactInGroupTitle").html(t('contact', 'Contact in group') + ":");
            $("#contactInGroupTabDiv").show();
            $("#contactInGroupMainTable").show();
            $("#contactInGroupLoaginImg").show();
            $("#contactInGroupTable").hide();
            $.post(OC.filePath("contact", "ajax", "contact_system_group.php"), {
                action : "getContactListBySystemGroupId",
                systemGroupId : systemGroupId,
            }, function(data) {
                if (data.status == "success") {
                    //除了樣版之外其它的tr都刪除
                    $("#selectAllContactInGroupItem").show();
                    $("#contactInGroupTable tr:gt(0)").remove();
                    //var resultArray = Contact.sortNickname(data.result);
                    var resultArray = data.result;
                    $.each(resultArray, function(index, val) {
                        var contact = val['contact'];
                        var nickname = val['nickname'];
                        if (contact.trim() != "") {
                            var cloneTr = $("#contactInGroupTable tr:first").clone();
                            cloneTr.find("#contactInGroup").val(contact);
                            cloneTr.find("#nicknameInGroup").html(nickname);
                            cloneTr.appendTo("#contactInGroupTable");
                            ContactInGroup.setHandler(cloneTr);
                        }
                        //隱藏樣版
                        $("#contactInGroupTable tr:first").hide();
                        $("#contactInGroupTable tr:gt(0)").show();
                    });
                    $("#contactInGroupTable").show();
                    $("#contactInGroupLoaginImg").hide();
                    $(".contactRelationButton").hide();
                    $(".contactRelationButton2").show();
                }
            });
        }
    },
    //預寫不用
    sortNickname : function(result) {
        var resultArray = new Array();
        $.each(result, function(index, val) {
            var contact = val.contact;
            var nickname = (val.nickname) ? val.nickname : contact;
            resultArray[index] = new Array();
            resultArray[index][0] = nickname;
            resultArray[index][1] = contact;
        });
        return resultArray.sort();
    },
    updateContactInGroup : function(contactInGroup) {
        //如果使用者有選取群組
        if (ContactGroup.selectedGroupId) {
            var groupId = ContactGroup.selectedGroupId;
            $.post(OC.filePath("contact", "ajax", "contact_group.php"), {
                action : "updateContactInGroup",
                groupId : groupId,
                contactId : contactInGroup,
            }, function(data) {
                if (data.status == "success") {
                    ContactInGroup.getContactListByGroupId();
                }
            });
        }
    },
    selectAll : function() {
        //樣版是隱藏的，所以選擇看的到的列表
        $(".contactInGroupTr:visible").each(function(index) {
            $(this).addClass("beSelected");
            ContactInGroup.setBgColor($(this));
        });
    },
    unSelectAll : function() {
        $("#contactInGroupTable .beSelected:visible").each(function(index) {
            $(this).removeClass("beSelected");
            ContactInGroup.setBgColor($(this));
        });
    },
    setDefaultHandler : function() {
        $("#selectAllContactInGroup").click(function() {
            if ($(this).prop('checked')) {
                ContactInGroup.selectAll();
            } else {
                ContactInGroup.unSelectAll();
            }
        });
    },
    setHandler : function(obj) {
        obj.mouseover(function() {
            $(this).addClass("beOvered");
            ContactInGroup.setBgColor($(this));
        });
        obj.mouseout(function() {
            $(this).removeClass("beOvered");
            ContactInGroup.setBgColor($(this));
        });
        obj.click(function(event) {
            if ($(this).hasClass("beSelected"))
                $(this).removeClass("beSelected");
            else
                $(this).addClass("beSelected");
            ContactInGroup.setBgColor($(this));
        });
    },
    setBgColor : function(obj) {
        if (obj.hasClass("beSelected")) {
            obj.css("background-color", ContactInGroup.selectedBgColor);
        } else if (obj.hasClass("beOvered")) {
            obj.css("background-color", ContactInGroup.activeBgColor);
        } else {
            obj.css("background-color", ContactInGroup.defaultBgColor);
        }
    }
};

var ContactRelationButton = {
    addContactToGroup : function() {
        var contactInGroupArray = $(".contactInGroupTr").map(function() {
            var contactId = $(this).find("#contactInGroup").val();
            if (contactId) {
                return contactId;
            }
        });
        $("#contactListTable .beSelected").each(function() {
            var contactId = $(this).find("#contactId").val();
            if ($.inArray(contactId, contactInGroupArray) < 0) {
                contactInGroupArray.push(contactId);
            }
        });
        contactInGroupArray.sort(function(a, b) {
            return a > b;
        });
        var contactInGroup = "";
        contactInGroupArray.each(function(key, val) {
            contactInGroup += val.trim() + ";";
        });
        ContactInGroup.updateContactInGroup(contactInGroup);
        Contact.unSelectAll();
    },
    removeContactFromGroup : function() {
        var contactInGroup = "";
        $(".contactInGroupTr").each(function(index) {
            var contactId = $(this).find("#contactInGroup").val();
            //如果有內容，而且是未選取(要保留)的
            if (contactId && !$(this).hasClass("beSelected")) {
                contactInGroup += contactId + ";";
            }
        });
        ContactInGroup.updateContactInGroup(contactInGroup);
    },
    // 將選取的系統群組聯絡人加入自己的聯絡人清單
    addSystemGroupContactToContact : function() {
        // 被選取的系統群組聯絡人
        var selectedContactArray = $("#contactInGroupTable .beSelected").map(function() {
            var contactId = $(this).find("#contactInGroup").val();
            if (contactId) {
                return contactId;
            }
        });
        // 將物件轉陣列
        selectedContactArray = $.makeArray(selectedContactArray);
        var contacts = selectedContactArray.join(';');
        Contact.addContacts(contacts);
        ContactInGroup.unSelectAll();
    },
    setHandler : function() {
        $("#addContactToGroupButton").click(function() {
            if (($("#contactListTable").find(".beSelected").length) > 0) {
                if (ContactGroup.selectedGroupId) {
                    ContactRelationButton.addContactToGroup();
                } else {
                    alert(t('contact', 'Please select a group'));
                }
            }
        });
        $("#removeContactFromGroupButton").click(function() {
            if (($("#contactInGroupTable").find(".beSelected").length) > 0) {
                ContactRelationButton.removeContactFromGroup();
            }
        });
        $("#addSystemGroupContactToContactButton").click(function() {
            if (($("#contactInGroupTable").find(".beSelected").length) > 0) {
                ContactRelationButton.addSystemGroupContactToContact();
            }
        });
    }
}; 