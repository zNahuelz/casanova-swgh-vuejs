import {Http} from "@/stores/http"

export const UserService = {
    endpoint: '/auth',
    sendRecoveryMail(email) {
        return Http.POST(`${this.endpoint}/recover_account`, email);
    },
    verifyRecoveryToken(token) {
        return Http.POST(`${this.endpoint}/verify_token`, token);
    },
    changePasswordWithToken(payload) {
        return Http.POST(`${this.endpoint}/change_password/token`, payload);
    },
    getCurrenUserProfile() {
        return Http.GET(`${this.endpoint}/profile`);
    }
};