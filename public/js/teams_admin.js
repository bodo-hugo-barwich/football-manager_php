import { createApp } from "/js/vue/petite-vue.es.js";

const addTeamURL = "/api/team";
const addPlayerURL = "/api/players";

// Form Fields
const addTeam = {
  name: {
    name: "name",
    label: "Name",
    value: "",
  },
  country: {
    name: "country_code",
    label: "Country Code",
    value: "",
  },
  balance: {
    name: "money_balance",
    label: "Money Balance",
    value: "",
  },
};
const addPlayer = {
  teamId: {
    name: "team_id",
    label: "Team ID",
    value: "",
  },
  name: {
    name: "name",
    label: "Name",
    value: "",
  },
  surname: {
    name: "surname",
    label: "Surname",
    value: "",
  },
};

const addTeamUsage = "Click the 'Create Team' button";
const addPlayerUsage = "Click the 'Create Player' button";

let vars = {
  show: true,
  selectedTeamId: -1,
};

function toggle() {
  this.vars.show = !this.vars.show;
}

function toggleAddTeamForm() {
  console.log("toggleAddTeamForm() - go ...");
  console.log("addTeam.show:", this.addTeam.show);
  console.log("addTeam.fields:", this.addTeam.fields);
  this.addTeam.show = !this.addTeam.show;
}

function toggleAddPlayerForm(team_id) {
  console.log("toggleAddPlayerForm() - go ...");
  console.log("team id:", team_id);
  console.log("addPlayer.show:", this.addPlayer.show);
  console.log("addPlayer.fields:", this.addPlayer.fields);

  if (this.vars.selectedTeamId != team_id) {
    this.vars.selectedTeamId = team_id;
    this.addPlayer.show = true;
    this.addPlayer.message = addPlayerUsage;

    for (let field in this.addPlayer.fields) {
      this.addPlayer.fields[field]["value"] = "";
    }

    this.addPlayer.fields.teamId.value = team_id;
  } else {
    this.addPlayer.show = !this.addPlayer.show;
  }
}

function submitTeam() {
  console.log("submitTeam() - go ...");

  let team = {};

  for (let field in this.addTeam.fields) {
    console.log("submitTeam() - field:", field);
    team[this.addTeam.fields[field].name] = this.addTeam.fields[field]["value"];
  }

  console.log("submitTeam() - team:", team);

  fetch(addTeamURL, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "*/*",
    },
    body: JSON.stringify(team),
  }).then(
    (res) => this.submittedAddTeam(res),
    (err) => this.failedAddTeam(err),
  );
  console.log("submitTeam() - fetch queued ...");

  this.addTeam.message = "Team - Create: submitting ...";
}

async function submittedAddTeam(response) {
  console.log("submittedAddTeam() - go ...");
  console.log("submittedAddTeam() - got response: ", response);

  if (response.status == 200) {
    let contents = await response.json();

    if (typeof response.bodyText == "string") {
      contents = JSON.parse(contents);
    }

    console.log("submittedAddTeam() - contents: ", contents);
    this.addTeam.message = `Team - Create: Team (${contents.id}) '${contents.name}' was created.`;

    setTimeout(() => {
      location.reload();
    }, 30000);

    this.$nextTick(() => {
      this.addTeam.message += "\nPage will be reloaded in 30 secs!";
    });
  } else {
    this.failedAddTeam(response);
  }
}

function failedAddTeam(error) {
  console.log("Teams API failed: ", error);

  this.addTeam.message =
    "Team - Create failed with [" + error.status + "]:\n" + error.statusText;
}

function submitPlayer() {
  console.log("submitPlayer() - go ...");

  let player = {};

  for (let field in this.addPlayer.fields) {
    console.log("submitPlayer() - field:", field);
    player[this.addPlayer.fields[field].name] =
      this.addPlayer.fields[field]["value"];
  }

  console.log("submitPlayer() - player:", player);

  fetch(addPlayerURL, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "*/*",
    },
    body: JSON.stringify(player),
  }).then(
    (res) => this.submittedAddPlayer(res),
    (err) => this.failedAddPlayer(err),
  );
  console.log("submitPlayer() - fetch queued ...");

  this.addPlayer.message = "Player - Create: submitting ...";
}

async function submittedAddPlayer(response) {
  console.log("submittedAddPlayer() - go ...");
  console.log("submittedAddPlayer() - got response: ", response);

  if (response.status == 200) {
    let contents = await response.json();

    if (typeof response.bodyText == "string") {
      contents = JSON.parse(contents);
    }

    console.log("submittedAddPlayer() - contents: ", contents);
    this.addPlayer.message = `Player - Create: Player (${contents.id}) '${contents.surname}' was created.`;

    let team_id = this.addPlayer.fields.teamId.value;

    for (let field in this.addPlayer.fields) {
      this.addPlayer.fields[field]["value"] = "";
    }

    this.addPlayer.fields.teamId.value = team_id;
  } else {
    this.failedAddPlayer(response);
  }
}

function failedAddPlayer(error) {
  console.log("Players API failed: ", error);

  this.addPlayer.message =
    "Player - Create failed with [" + error.status + "]:\n" + error.statusText;
}

function mounted(team_id, form, show) {
  console.log("mounted() - go ...");
  console.log(`team id: '${team_id}'; form: '${form}'; show: ${show}`);

  if (form in this) {
    this[form].show = show;
  }

  this.$nextTick(function () {
    // Code that will run only after the
    // entire view has been rendered

    console.log("dom next tick.");

    console.log("addTeam.fields:", addTeam.fields);
  });
}

//Vue.JS Engine Object
let app = createApp({
  toggle,
  toggleAddTeamForm,
  toggleAddPlayerForm,
  vars: vars,
  menu: { show: false },
  addTeam: {
    show: false,
    fields: addTeam,
    message: addTeamUsage,
  },
  addPlayer: {
    show: false,
    fields: addPlayer,
    message: addPlayerUsage,
  },
  mounted,
  submitTeam,
  submittedAddTeam,
  failedAddTeam,
  submitPlayer,
  submittedAddPlayer,
  failedAddPlayer,
}).mount();

//Application Container Box
console.log(app);

console.log("done.");
