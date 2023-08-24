const state = {
    logged: undefined,
    token: undefined,
    userInfo: undefined,
};

const mutations = {
  SET_LOGGED: (state, logged) => {
    state.logged = logged;
  },
  SET_TOKEN: (state, token) => {
    state.token = token;
  },
  SET_USER_INFO: (state, userInfo) => {
    state.userInfo = userInfo;
  },
};

const getters = {
  logged: state => {
    return state.logged === undefined ? JSON.parse(localStorage.getItem('session@logged')) : state.logged;
  },
  token: state => {
    return state.token === undefined ? localStorage.getItem('session@token') : state.token;
  },
  userInfo: state => {
    return state.userInfo === undefined ? JSON.parse(localStorage.getItem('session@userInfo')) : state.userInfo;
  },
};

export default {
  namespaced: true,
  state,
  mutations,
  // actions,
  getters,
};
