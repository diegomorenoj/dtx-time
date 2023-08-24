import axios from 'axios';
export default class UserService {
    all () {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/users`).then(res => {
            return res.data;
        });
    }

    update (data, id) {
        return axios.put(`${process.env.VUE_APP_RUTA_API}/users/${id}`, data).then(res => {
            console.log('UPDATE USER', res.data);
            return res.data;
        });
    }

    importLdapUsers () {
        return axios.get(`${process.env.VUE_APP_RUTA_API}/ldap/importusers`).then(res => {
            console.log('IMPORT USER', res.data);
            return res.data;
        });
    }
}
