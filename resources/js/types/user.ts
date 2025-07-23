export interface User {
    id: BigInteger;
    uuid: string;
    name: string;
    email: string;
    password?: string;
    email_verified_at: string,
    phone?: BigInteger;
    role_id: BigInteger;
}
