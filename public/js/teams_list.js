import { createApp } from "/js/vue/petite-vue.es.js";

let vars = {
  players_url: "",
  purchase_url: "",
  fetchResult: "Team Players: Click link to load",
};

function fetchPlayers(team_id) {
  console.log("fetchPlayers() - go ...");
  console.log("team id:", team_id);

  this.vars.teamID = team_id;
  this.vars.fetchResult =
    "Team (" + this.vars.teamID + ") Players: fetching ...";

  this.teams[team_id] = { players: [], fetchResult: this.vars.fetchResult };

  fetch(this.vars.players_url + "?team_id=" + team_id).then(
    (res) => this.displayPlayers(res),
    (err) => this.failedPlayers(err),
  );
  console.log("fetchPlayers() - fetch queued ...");
}

async function displayPlayers(response) {
  console.log("displayPlayers() - go ...");
  console.log("displayPlayers() - got response: ", response);

  let contents = await response.json();

  if (typeof response.bodyText == "string") {
    contents = JSON.parse(contents);
  }

  console.log("displayPlayers() - contents: ", contents);

  for (let player_index of Object.keys(contents)) {
    contents[player_index]["updated"] = "new";
    contents[player_index]["purchaseURL"] = this.vars.purchase_url.replace(
      ":id:",
      contents[player_index].id,
    );
  }

  this.teams[this.vars.teamID].players = contents;
  this.teams[this.vars.teamID].updated = "loaded";

  if (contents.length > 0) {
    this.teams[this.vars.teamID].fetchResult =
      "Team (" +
      this.vars.teamID +
      ") Players: found '" +
      contents.length +
      "' players:";
  } else {
    this.teams[this.vars.teamID].fetchResult =
      "Team (" + this.vars.teamID + ") Players: Team has no players.";
  }

  console.log(
    "displayPlayers() - players: ",
    this.teams[this.vars.teamID].players,
  );
}

function failedPlayers(error) {
  console.log("Players API failed: ", error);

  if (this.vars.teamID !== null) {
    this.vars.fetchResult =
      `Team (${this.vars.teamID}) - Players failed:<br />\n` + error.bodyText;
  }
}

function mounted(team_id, players_url, purchase_url) {
  console.log("mounted() - go ...");
  console.log("team id:", team_id);
  console.log("players url:", players_url);
  console.log("purchase url:", purchase_url);

  this.vars.show = false;

  if (typeof team_id !== "undefined") {
    this.teams[team_id] = {
      players: [],
      fetchResult: `Team (${team_id}) Players: click link`,
      updated: "new",
    };

    console.log("teams:", this.teams);
  }

  if (typeof players_url !== "undefined") {
    this.vars.players_url = players_url;
  }

  if (typeof purchase_url !== "undefined") {
    this.vars.purchase_url = purchase_url;
  }

  this.$nextTick(function () {
    // Code that will run only after the
    // entire view has been rendered

    console.log("dom next tick.");
  });

  console.log("mounted() - done.");
}

let app = createApp({
  vars: vars,
  teams: {},
  mounted,
  fetchPlayers,
  displayPlayers,
  failedPlayers,
}).mount();

//Application Container Box
console.log(app);

console.log("done.");
