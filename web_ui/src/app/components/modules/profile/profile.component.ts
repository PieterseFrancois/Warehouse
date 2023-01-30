import { Component, OnDestroy } from '@angular/core';
import { Subject, takeUntil } from 'rxjs';
import { ApiService } from 'src/app/global/services/api.service';
import { UserState } from 'src/app/global/state/user-state';
import { API_response, User } from 'src/app/global/types';

@Component({
    selector: 'app-profile',
    templateUrl: './profile.component.html',
    styleUrls: ['./profile.component.scss']
})

export class ProfileComponent implements OnDestroy {

    new_name: string = undefined;
    new_email: string = undefined;

    private destroy$: Subject<boolean> = new Subject();

    constructor( private user_state: UserState, private api : ApiService ) {

        this.user_state.current_user$
        .pipe( takeUntil( this.destroy$ ) )
        .subscribe(
            ( user: User ) => {

                if ( user === undefined ) return; 

                this.new_name = user.name;
                this.new_email = user.email;  
                
            }
        );
    }

    ngOnDestroy(): void {
        this.destroy$.next(true);
        this.destroy$.complete();
    }

    isChanged() {
        
        return ( this.new_name !== this.user_state.current_user.name ) || ( this.new_email !== this.user_state.current_user.email );

    }

    save() {

        const user : User = {
            email : this.new_email,
            name : this.new_name,
            id : this.user_state.current_user.id,
        }

        //call types.ts service and pass endpoint and object
        this.api.post( 'users/update', user ).subscribe( 
            ( response: API_response ) => {
                
                //thow error if not successfull
                if ( response.success !== true )
                {   
                    const confirmation_reset = confirm( response.message + ' Do you wish to reset your details?' );

                    console.log( this.user_state.current_user );

                    if ( confirmation_reset ) 
                        this.cancel();

                    return;
                }
                
                //temp
                alert( 'Details updated successfully' );

                this.updateState();
            }
        );    
    }

    updateState() {

        var user = this.user_state.current_user;
        
        user.email = this.new_email;
        user.name = this.new_name;

        this.user_state.setUserState( user );        
    }

    cancel() {

        this.new_name = this.user_state.current_user.name;
        this.new_email = this.user_state.current_user.email;
    }

}
