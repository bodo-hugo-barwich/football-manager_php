import { createApp } from "/js/vue/petite-vue.es.js";

const listTeamsURL = "/api/teams";
const checkoutPlayerURL = "/api/players/:id:/purchase";

// Form Fields
const purchasePlayer = {
  currentTeamId: {
    name: "current_team_id",
    label: "Current Team ID",
    value: "",
  },
  purchasingTeamId: {
    name: "purchasing_team_id",
    label: "Purchasing Team ID",
    value: "",
  },
  purchasingTeamBalance: {
    name: "purchasing_team_balance",
    label: "Purchasing Team Balance",
    value: "",
  },
  price: {
    name: "price",
    label: "Price",
    value: "",
  },
};

const listTeamsUsage = "Select the Purchasing Team from the Dropdown";
const purchasePlayerUsage = "Please, enter a price for the purchase";

let vars = {
  listTeamsURL: listTeamsURL,
  listTeamsMessage: listTeamsUsage,
  checkoutPlayerURL: checkoutPlayerURL,
  selectedTeamId: -1,
};

function fetchTeams() {
  console.log("fetchTeams() - go ...");

  fetch(this.vars.listTeamsURL).then(
    (res) => this.listTeams(res),
    (err) => this.failedTeams(err),
  );
  console.log("fetchTeams() - fetch queued ...");

  this.vars.listTeamsMessage = "Teams - List: fetching ...";
}

async function listTeams(response) {
  console.log("listTeams() - go ...");
  console.log("listTeams() - got response: ", response);

  if (response.status == 200) {
    let contents = await response.json();

    if (typeof response.bodyText == "string") {
      contents = JSON.parse(contents);
    }

    console.log("listTeams() - contents: ", contents);

    for (let team_idx in Object.keys(contents)) {
      contents[team_idx].moneyBalance = parseFloat(
        contents[team_idx].moneyBalance,
      );

      this.teams[contents[team_idx].id] = contents[team_idx];
    }

    console.log("listTeams() - teams: ", this.teams);

    this.vars.listTeamsMessage = listTeamsUsage;
  } else {
    this.failedTeams(response);
  }
}

function failedTeams(error) {
  console.log("Teams API failed: ", error);

  this.addTeam.message =
    "Teams - List failed with [" + error.status + "]:\n" + error.statusText;
}

function selectTeam() {
  console.log("selectTeam() - go ...");
  console.log("team id:", this.vars.selectedTeamId);

  this.purchasePlayer.fields.purchasingTeamId.value = this.vars.selectedTeamId;

  this.$nextTick(() => {
    console.log("selectTeam() - nextTick() - go ...");
    console.log("team id:", this.vars.selectedTeamId);

    if (typeof this.teams[this.vars.selectedTeamId] !== "undefined") {
      this.purchasePlayer.fields.purchasingTeamId.value =
        this.vars.selectedTeamId;
      this.purchasePlayer.fields.purchasingTeamBalance.value =
        this.teams[this.vars.selectedTeamId].moneyBalance;
    } else {
      this.purchasePlayer.fields.purchasingTeamId.value = "";
      this.purchasePlayer.fields.purchasingTeamBalance.value = "";
    }
  });
}

function checkoutPlayer() {
  console.log("checkoutPlayer() - go ...");

  let purchase = {};

  for (let field in this.purchasePlayer.fields) {
    console.log("checkoutPlayer() - field:", field);
    purchase[this.purchasePlayer.fields[field].name] =
      this.purchasePlayer.fields[field]["value"];
  }
  console.log("checkoutPlayer() - purchase:", purchase);

  console.log("pr prs:", parseFloat(this.purchasePlayer.fields.price.value));

  if (isNaN(parseFloat(purchase.price))) {
    this.purchasePlayer.message =
      "The Purchase Price must be a valid floatpoint number!";

    return;
  } else {
    purchase.price = parseFloat(purchase.price);
  }

  if (
    isNaN(parseInt(purchase.purchasing_team_id)) ||
    typeof this.teams[purchase.purchasing_team_id] === "undefined"
  ) {
    this.purchasePlayer.message = "The Purchasing Team must have a valid ID!";

    return;
  }

  if (purchase.purchasing_team_id == this.player.team_id) {
    this.purchasePlayer.message =
      "The Purchasing Team must not be the same as the Current Team!";

    return;
  }

  let purchasingTeam = this.teams[purchase.purchasing_team_id];

  console.log("checkoutPlayer() - prch team 0:", purchasingTeam);

  if (purchasingTeam.moneyBalance < purchase.price) {
    this.purchasePlayer.message = `The Purchasing Team (${purchasingTeam.id}) '${purchasingTeam.name}' cannot afford the deal!`;

    return;
  }

  let purchasePlayerRequest = {
    team_id: purchasingTeam.id,
    price: purchase.price,
  };

  console.log("checkoutPlayer() - prch ply req:", purchasePlayerRequest);

  fetch(this.vars.checkoutPlayerURL, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "*/*",
    },
    body: JSON.stringify(purchasePlayerRequest),
  }).then(
    (res) => this.editedPlayer(res),
    (err) => this.failedEditPlayer(err),
  );
  console.log("checkoutPlayer() - update queued ...");

  this.purchasePlayer.message = "Player - Checkout: Updating ...";
}

async function editedPlayer(response) {
  console.log("editedPlayer() - go ...");
  console.log("editedPlayer() - got response: ", response);

  if (response.status == 200) {
    let contents = await response.json();

    if (typeof response.bodyText == "string") {
      contents = JSON.parse(contents);
    }

    console.log("editedPlayer() - contents: ", contents);

    this.purchasePlayer.message = `Player - Purchase: Player (${contents.id}) '${contents.surname}' was updated.`;

    setTimeout(() => {
      location.reload();
    }, 30000);

    this.$nextTick(() => {
      this.purchasePlayer.message += "\nPage will be reloaded in 30 secs!";
    });
  } else {
    this.failedEditPlayer(response);
  }
}

function failedEditPlayer(error) {
  console.log("Player API failed: ", error);

  this.purchasePlayer.message =
    "Player - Update failed with [" + error.status + "]:\n" + error.statusText;
}

function mounted(player, teams_url, player_edit_url) {
  console.log("mounted() - go ...");
  console.log("player: ", player);
  console.log(`teams url: '${teams_url}'`);

  if (typeof player !== "undefined") {
    this.player = player;
    this.purchasePlayer.fields.currentTeamId.value = player.team_id;
  }

  if (typeof teams_url !== "undefined") {
    this.vars.listTeamsURL = teams_url;

    this.vars.listTeamsMessage = "Teams - List: fetching ...";

    this.fetchTeams();
  }

  if (typeof player_edit_url !== "undefined") {
    this.vars.checkoutPlayerURL = player_edit_url;
  }

  this.$nextTick(function () {
    // Code that will run only after the
    // entire view has been rendered

    console.log("dom next tick.");
  });
}

//Vue.JS Engine Object
let app = createApp({
  vars: vars,
  teams: {},
  player: {},
  purchasePlayer: {
    fields: purchasePlayer,
    message: purchasePlayerUsage,
  },
  mounted,
  fetchTeams,
  listTeams,
  failedTeams,
  selectTeam,
  checkoutPlayer,
  editedPlayer,
  failedEditPlayer,
}).mount();

// Vue.JS Application
console.log(app);

console.log("done.");
